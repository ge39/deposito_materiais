<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    public function index(): View
    {
        $empresas = Empresa::where('ativo', true)
            ->orderBy('id')
            ->get();

        return view('empresa.index', compact('empresas'));
    }

    public function create(): View
    {
        return view('empresa.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarDados($request);
        $dados['ativo'] = $request->boolean('ativo');
        $dados = $this->aplicarCoordenadas($dados);

        Empresa::create($dados);

        return redirect()
            ->route('empresa.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresa.edit', compact('empresa'));
    }

    public function update(
        Request $request,
        Empresa $empresa
    ): RedirectResponse {
        $dados = $this->validarDados($request, $empresa);
        $dados['ativo'] = $request->boolean('ativo');
        $dados = $this->aplicarCoordenadas(
            $dados,
            $empresa
        );

        $empresa->update($dados);

        return redirect()
            ->route('empresa.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function geocodificarEndereco(
        Request $request
    ): JsonResponse {
        $dados = $request->validate([
            'cep' => 'nullable|string|max:10',
            'endereco' => 'required|string|max:255',
            'numero' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:50',
            'cidade' => 'required|string|max:50',
            'estado' => 'required|string|size:2',
        ]);

        try {
            $resultado = $this->consultarCoordenadas(
                $dados
            );

            return response()->json([
                'success' => true,
                ...$resultado,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())
                    ->flatten()
                    ->first(),
            ], 422);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível consultar a localização neste momento.',
            ], 500);
        }
    }

    public function desativar(
        Empresa $empresa
    ): RedirectResponse {
        $empresa->ativo = false;
        $empresa->save();

        return redirect()
            ->route('empresa.index')
            ->with('success', 'Empresa desativada com sucesso!');
    }

    public function ativar(
        Empresa $empresa
    ): RedirectResponse {
        $empresa->ativo = true;
        $empresa->save();

        return redirect()
            ->route('empresa.index')
            ->with('success', 'Empresa ativada com sucesso!');
    }

    public function desativadas(): View
    {
        $empresas = Empresa::where('ativo', false)
            ->orderBy('id')
            ->get();

        return view(
            'empresa.desativadas',
            compact('empresas')
        );
    }

    private function validarDados(
        Request $request,
        ?Empresa $empresa = null
    ): array {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('empresa', 'cnpj')
                    ->ignore($empresa?->id),
            ],
            'inscricao_estadual' =>
                'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:50',
            'bairro' => 'nullable|string|max:50',
            'cidade' => 'nullable|string|max:50',
            'estado' => 'nullable|string|size:2',
            'cep' => 'nullable|string|max:10',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'site' => 'nullable|string|max:100',
            'ativo' => 'nullable|boolean',
        ]);
    }

    private function aplicarCoordenadas(
        array $dados,
        ?Empresa $empresa = null
    ): array {
        $camposEndereco = [
            'cep',
            'endereco',
            'numero',
            'bairro',
            'cidade',
            'estado',
        ];

        $enderecoAlterado = $empresa === null;

        if ($empresa !== null) {
            foreach ($camposEndereco as $campo) {
                if (
                    trim((string) ($dados[$campo] ?? ''))
                    !== trim((string) $empresa->{$campo})
                ) {
                    $enderecoAlterado = true;
                    break;
                }
            }
        }

        if (
            ! $enderecoAlterado
            && $empresa?->latitude !== null
            && $empresa?->longitude !== null
        ) {
            $dados['latitude'] = $empresa->latitude;
            $dados['longitude'] = $empresa->longitude;

            return $dados;
        }

        if (! $this->possuiEnderecoGeocodificavel($dados)) {
            $dados['latitude'] = null;
            $dados['longitude'] = null;

            return $dados;
        }

        $coordenadas = $this->consultarCoordenadas($dados);
        $dados['latitude'] = $coordenadas['latitude'];
        $dados['longitude'] = $coordenadas['longitude'];

        return $dados;
    }

    private function possuiEnderecoGeocodificavel(
        array $dados
    ): bool {
        return trim((string) ($dados['endereco'] ?? '')) !== ''
            && trim((string) ($dados['cidade'] ?? '')) !== ''
            && trim((string) ($dados['estado'] ?? '')) !== '';
    }

    private function consultarCoordenadas(array $dados): array
    {
        $partes = array_filter([
            trim((string) ($dados['endereco'] ?? '')),
            trim((string) ($dados['numero'] ?? '')),
            trim((string) ($dados['bairro'] ?? '')),
            trim((string) ($dados['cidade'] ?? '')),
            strtoupper(trim((string) ($dados['estado'] ?? ''))),
            trim((string) ($dados['cep'] ?? '')),
            'Brasil',
        ]);

        $enderecoCompleto = implode(', ', $partes);
        $chaveCache = 'empresa:geocodificacao:'
            . sha1(Str::lower($enderecoCompleto));

        return Cache::remember(
            $chaveCache,
            now()->addDays(
                max(
                    1,
                    (int) config(
                        'openstreetmap.geocoding_cache_days',
                        30
                    )
                )
            ),
            function () use ($enderecoCompleto): array {
                $resposta = Http::acceptJson()
                    ->withHeaders([
                        'User-Agent' => (string) config(
                            'openstreetmap.user_agent',
                            config('app.name', 'deposito_materiais')
                            . '/1.0'
                        ),
                        'Accept-Language' => 'pt-BR,pt;q=0.9',
                    ])
                    ->timeout(15)
                    ->get(
                        (string) config(
                            'openstreetmap.geocoding_url'
                        ),
                        [
                            'q' => $enderecoCompleto,
                            'format' => 'jsonv2',
                            'limit' => 1,
                            'countrycodes' => 'br',
                            'addressdetails' => 1,
                        ]
                    );

                if (! $resposta->successful()) {
                    throw ValidationException::withMessages([
                        'endereco' => 'O serviço de geolocalização não respondeu corretamente.',
                    ]);
                }

                $resultado = collect($resposta->json())
                    ->first();

                if (! $resultado) {
                    throw ValidationException::withMessages([
                        'endereco' => 'Não foi possível localizar o endereço informado.',
                    ]);
                }

                $latitude = round(
                    (float) ($resultado['lat'] ?? 0),
                    7
                );

                $longitude = round(
                    (float) ($resultado['lon'] ?? 0),
                    7
                );

                if (
                    $latitude < -90
                    || $latitude > 90
                    || $longitude < -180
                    || $longitude > 180
                ) {
                    throw ValidationException::withMessages([
                        'endereco' => 'O serviço retornou coordenadas inválidas.',
                    ]);
                }

                return [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'endereco_formatado' =>
                        $resultado['display_name']
                        ?? $enderecoCompleto,
                ];
            }
        );
    }
}
@props([
    'title',
    'icon' => 'bi-bar-chart',
    'value' => null,
    'description' => null,
    'badge' => null,
    'badgeClass' => 'text-bg-secondary',
    'modal' => null,
    'action' => 'Ver detalhes',
    'actionRoute' => null,
    'accentClass' => 'text-primary',
])

<div class="card jmf-bi-card h-100">

    <div class="card-body">

        <div class="jmf-bi-card-header">

            <div class="jmf-bi-card-title">

                <i class="bi {{ $icon }} {{ $accentClass }}"></i>

                <span>
                    {!! $title !!}
                </span>

            </div>

        </div>


        @if(!is_null($value))

            <div class="jmf-bi-card-value">
                {!! $value !!}
            </div>

        @endif


        @if($description)

            <div class="jmf-bi-card-description">
                {!! $description !!}
            </div>

        @endif


        <div class="jmf-bi-card-footer">

            <div>

                @if($badge)

                    <span class="badge {{ $badgeClass }}">
                        {!! $badge !!}
                    </span>

                @endif

            </div>


            <div>

                @if($modal)

                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#{{ $modal }}"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ $action }}
                    </button>

                @elseif($actionRoute)

                    <a
                        href="{{ $actionRoute }}"
                        class="btn btn-sm btn-primary"
                    >
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ $action }}
                    </a>

                @endif

            </div>

        </div>

    </div>

</div>
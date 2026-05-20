window.carrinho = window.carrinho || [];

document.addEventListener('DOMContentLoaded', function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const totalGeralEl = document.getElementById('totalGeral');
  const totalModalEl = document.getElementById('total-venda-modal');
  const modalEl = document.getElementById('modalFinalizarVenda');
  const restanteEl = document.getElementById('valor-restante');
  const trocoEl = document.getElementById('valor-troco');
  const btnFinalizar = document.getElementById('btnFinalizar');
  
  // Variável global interna para lembrar qual foi o último input focado pelo operador
  window.__pdvUltimaFormaFocada = 'dinheiro';

  if (!totalGeralEl || !totalModalEl || !modalEl) {
    console.warn('Elementos principais do PDV não encontrados');
    return;
  }

  const inputsPagamento = modalEl.querySelectorAll('.pagamento-modal');
  let modal = null;

  if (typeof bootstrap !== 'undefined') {
    modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  } else {
    console.warn('Bootstrap não carregado');
  }

  // ========================================== //
  // HELPERS CORRIGIDOS                         //
  // ========================================== //
  function obtenerTotalVenda() {
    return parseFloat(totalGeralEl.textContent.replace(/\D/g, '')) / 100 || 0;
  }

  function obtenerSaldoCarteira() {
    return window.cliente?.saldo || 0;
  }

  function calcularRestante(inputAtual = null) {
    const total = obtenerTotalVenda();
    let s = 0;
    inputsPagamento.forEach(i => {
      if (i !== inputAtual) {
        s += parseFloat(i.value) || 0;
      }
    });
    const r = total - s;
    return r > 0 ? r : 0;
  }

  // ========================================================== //
  // 🔥 HELPER INTELIGENTE: DISTRIBUI EXCEDENTE NA FORMA CORRETA//
  // ========================================================== //
  function recalcularETransferirExcedenteCarteira(inputCarteira) {
    const saldoDisponivel = obtenerSaldoCarteira();
    const statusCredito = window.cliente?.status;
    const valorDigitado = parseFloat(inputCarteira.value) || 0;
    let formaDestino = window.__pdvUltimaFormaFocada === 'carteira' ? 'dinheiro' : window.__pdvUltimaFormaFocada;
    const inputDestino = modalEl.querySelector(`.pagamento-modal[data-forma="${formaDestino}"]`);
    
    if (!inputDestino) return;
    const nomeFormaAmigavel = formaDestino.replace('_', ' ').toUpperCase();

    // 🔴 CENÁRIO 1: Crédito bloqueado ou sem saldo
    if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
      if (valorDigitado > 0) {
        const valorAtualDestino = parseFloat(inputDestino.value) || 0;
        inputDestino.value = (valorAtualDestino + valorDigitado).toFixed(2);
        inputCarteira.value = '';
        inputDestino.dispatchEvent(new Event('input', { bubbles: true }));
        inputDestino.focus();
        alert(`Este cliente está com o crediário bloqueado ou sem saldo. O valor foi transferido para ${nomeFormaAmigavel}.`);
      }
      return;
    }

    // 🟡 CENÁRIO 2: Valor digitado maior que o saldo restante da carteira
    if (valorDigitado > saldoDisponivel) {
      const excedente = valorDigitado - saldoDisponivel;
      inputCarteira.value = saldoDisponivel.toFixed(2);
      const valorAtualDestino = parseFloat(inputDestino.value) || 0;
      inputDestino.value = (valorAtualDestino + excedente).toFixed(2);
      inputDestino.dispatchEvent(new Event('input', { bubbles: true }));
      inputDestino.focus();
      alert(`O saldo da carteira é de R$ ${saldoDisponivel.toFixed(2).replace('.', ',')}. O restante (R$ ${excedente.toFixed(2).replace('.', ',')}) foi direcionado para ${nomeFormaAmigavel}.`);
    }
  }

  // ========================================== //
  // DINHEIRO: ZERA SE TOTAL JÁ FECHADO         //
  // ========================================== //
  function controlarDinheiroQuandoFechado() {
    const totalVenda = obtenerTotalVenda();
    let s = 0;
    inputsPagamento.forEach(i => {
      const v = parseFloat(i.value) || 0;
      if (v > 0) s += v;
    });
    const inputDinheiro = document.querySelector('.pagamento-modal[data-forma="dinheiro"]');
    if (!inputDinheiro) return;
    if (Math.abs(s - totalVenda) < 0.01) {
      if (parseFloat(inputDinheiro.value) > 0) {
        inputDinheiro.value = '0.00';
        trocoEl.textContent = (0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        atualizarResumo();
      }
    }
  }

   // ==========================================
    // ATALHOS TECLADO: PREENCHE EXATAMENTE O RESTANTE DA FOTO
    // ==========================================
    document.addEventListener('keydown', function (e) {
        if (!modalEl || !modalEl.classList.contains('show')) return;
        
        const tecla = e.key.toLowerCase();
        window.__pdvBufferForma = (window.__pdvBufferForma || '') + tecla;
        window.__pdvBufferForma = window.__pdvBufferForma.slice(-2);

        let forma = null;
        switch (window.__pdvBufferForma) {
            case 'dd': forma = 'dinheiro'; break;
            case 'cc': forma = 'cartao_credito'; break;
            case 'cd': forma = 'cartao_debito'; break;
            case 'pi': forma = 'pix'; break;
            case 'ca': forma = 'carteira'; break;
        }

        if (!forma) return;
        
        const input = modalEl.querySelector(`.pagamento-modal[data-forma="${forma}"]`);
        if (!input) return;

        e.preventDefault();

        // 🔥 Captura os R$ 28,00 da foto direto do elemento 'valor-restante'
        const textoRestante = restanteEl.textContent
            .replace(/\./g, '')
            .replace(',', '.')
            .replace(/[^\d.]/g, '');
        
        let valorRestante = parseFloat(textoRestante) || 0;

        const valorAtualDoCampo = parseFloat(input.value) || 0;
        let valorParaPreencher = valorRestante + valorAtualDoCampo;

        // Se pressionar 'CA', força a limitação ao saldo disponível (R$ 20,00 da Maria)
        if (forma === 'carteira') {
            const saldoDisponivel = obtenerSaldoCarteira();
            const statusCredito = window.cliente?.status;

            if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
                window.__pdvBufferForma = '';
                return;
            }
            if (valorParaPreencher > saldoDisponivel) {
                valorParaPreencher = saldoDisponivel; // No caso da foto, crava em 20.00
            }
        }

        input.focus();
        input.value = valorParaPreencher.toFixed(2);
        input.select();
        
        input.dispatchEvent(new Event('input', { bubbles: true }));
        window.__pdvBufferForma = '';
    });

  // ========================================== //
  // REGRAS DE PERMISSÃO E EXIBIÇÃO DE CRÉDITO  //
  // ========================================== //
  function aplicarFormasPermitidas() {
    if (!window.cliente) return;
    const saldo = obtenerSaldoCarteira();
    const statusCredito = window.cliente.status;
    inputsPagamento.forEach(input => {
      const forma = input.dataset.forma;
      if (forma === 'carteira') {
        input.disabled = false;
        if (statusCredito !== 'ativo') {
          input.placeholder = 'Crédito Bloqueado';
        } else if (saldo <= 0) {
          input.placeholder = 'Sem saldo disponível';
        } else {
          input.placeholder = `Até R$ ${saldo.toFixed(2)}`;
        }
      }
    });
  }

  function atualizarResumo() {
    const total = obtenerTotalVenda();
    let s = 0;
    inputsPagamento.forEach(i => {
      s += parseFloat(i.value) || 0;
    });
    
    let restante = total - s;
    let troco = 0;
    if (restante < 0) {
      troco = Math.abs(restante);
      restante = 0;
    }
    
    if (restanteEl) {
      restanteEl.textContent = restante.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
    if (trocoEl) {
      trocoEl.textContent = troco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    const inputCarteira = modalEl.querySelector('[data-forma="carteira"]');
    if (inputCarteira) {
      const saldo = obtenerSaldoCarteira();
      const statusCredito = window.cliente?.status;
      if (statusCredito !== 'ativo') {
        inputCarteira.placeholder = 'Crédito Bloqueado';
      } else if (saldo <= 0) {
        inputCarteira.placeholder = 'Saldo Insuficiente';
      } else {
        inputCarteira.placeholder = `Até R$ ${saldo.toFixed(2)}`;
      }
    }
  }
  
  document.addEventListener('DOMContentLoaded', function () {
      // 1. Elementos globais do seu PDV (Mapeie todos aqui no topo)
      const modalEl = document.getElementById('seu-modal-id'); 
      const restanteEl = document.getElementById('valor-restante'); // Sua matriz de saldo
      const btnFinalizar = document.getElementById('btn-finalizar-venda'); // Botão finalizar
      const inputsPagamento = document.querySelectorAll('.pagamento-modal'); // Seus inputs

      // 2. Suas funções de cálculo (Exemplos)
      function obterTotalVenda() { /* ... */ }
      function atualizarResumo() { /* ... */ }

      // ----------------------------------------------------
      // COLE A FUNÇÃO DO ENTER AQUI (Abaixo das declarações)
      // ----------------------------------------------------
      inputsPagamento.forEach(input => {
        input.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') {
            // 1. Formata e valida o input atual
            let valorTexto = this.value.replace(',', '.');
            const n = parseFloat(valorTexto);
            if (!isNaN(n) && n > 0) {
              this.value = n.toFixed(2);
            } else {
              this.value = '';
            }

            // 2. Força a atualização dos cálculos da tela
            if (typeof atualizarResumo === 'function') {
              atualizarResumo();
            }

            // 3. Captura o valor restante atualizado DIRETO da tela (sua matriz)
            const textoRestante = restanteEl.textContent
                .replace(/\./g, '')
                .replace(',', '.')
                .replace(/[^\d.]/g, '');
            
            const valorRestanteReal = parseFloat(textoRestante) || 0;

            // 4. Se o saldo restante zerou, foca no botão
            if (valorRestanteReal <= 0) {
              e.preventDefault(); 
              
              if (btnFinalizar) {
                btnFinalizar.focus(); 
              }
            }
          }
        });
      });

      // ----------------------------------------------------
      // ABAIXO VOCÊ DEIXA A SUA FUNÇÃO DE ATALHOS (dd, cc, pi...)
      // ----------------------------------------------------
      document.addEventListener('keydown', function (e) {
          // ... (sua função de atalhos que você enviou antes)
      });

  });

  // ========================================== //
  // CARREGAMENTO DA API FINANCEIRA            //
  // ========================================== //
  function carregarClienteFinanceiro(clienteId) {
    if (!clienteId || clienteId == 6) {
      window.cliente = { status: 'bloqueado', saldo: 0 };
      aplicarFormasPermitidas();
      atualizarResumo();
      return;
    }
    fetch(`/api/cliente/financeiro/${clienteId}`)
      .then(res => {
        if (!res.ok) throw new Error('Erro na API');
        return res.json();
      })
      .then(data => {
        window.cliente = {
          id: clienteId,
          nome: data.cliente?.nome ?? data.cliente ?? '',
          saldo: Number(data.saldo ?? data.saldo_atual ?? 0),
          limite: Number(data.limite ?? data.limite_credito ?? 0),
          credito_usado: Number(data.credito_usado ?? 0),
          formas: data.formas_pagamento ?? [],
          status: data.status ?? data.credito_status ?? 'ativo'
        };
        aplicarFormasPermitidas();
        atualizarResumo();
      })
      .catch(err => console.error('Erro cliente financeiro:', err));
  }

  function abrirModalFinalizar() {
    const inputCliente = document.querySelector('input[name="cliente_id"]') || document.getElementById('input-cliente-id');
    const clienteId = inputCliente?.value;
    carregarClienteFinanceiro(clienteId);
    const total = obtenerTotalVenda();
    totalModalEl.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    inputsPagamento.forEach(i => i.value = '');
    window.__pdvUltimaFormaFocada = 'dinheiro';
    atualizarResumo();
    modal?.show();
    setTimeout(() => {
      if (inputsPagamento && inputsPagamento.length > 0) {
        inputsPagamento[0].focus();
      }
    }, 300);
  }

  // ========================================== //
  // INPUTS EVENTS COM CAPTURA DE FOCO ATIVO    //
  // ========================================== //
  inputsPagamento.forEach(input => {
    input.addEventListener('focus', function() {
      window.__pdvUltimaFormaFocada = this.dataset.forma;
    });

    input.addEventListener('input', function () {
      const forma = this.dataset.forma;
      let valor = parseFloat(this.value) || 0;
      const restanteSemEsteInput = calcularRestante(this);

      if (forma === 'carteira') {
        const saldoDisponivel = obtenerSaldoCarteira();
        const statusCredito = window.cliente?.status;
        if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
          this.value = '';
          atualizarResumo();
          return;
        }
        if (valor > saldoDisponivel) {
          this.value = saldoDisponivel.toFixed(2);
          valor = saldoDisponivel;
        }
      }

      if (forma !== 'dinheiro' && valor > restanteSemEsteInput) {
        this.value = restanteSemEsteInput.toFixed(2);
      }
      atualizarResumo();
    });

    input.addEventListener('blur', function () {
      let valor = this.value.replace(',', '.');
      const n = parseFloat(valor);
      if (isNaN(n) || n <= 0) {
        this.value = '';
        atualizarResumo();
        return;
      }
      this.value = n.toFixed(2);
      atualizarResumo();
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        let valorTexto = this.value.replace(',', '.');
        const n = parseFloat(valorTexto);
        if (!isNaN(n) && n > 0) {
          this.value = n.toFixed(2);
        } else {
          this.value = '';
        }
        const totalVenda = obterTotalVenda();
        let somaTotalInputs = 0;
        inputsPagamento.forEach(i => {
          somaTotalInputs += parseFloat(i.value) || 0;
        });

        let valorRestanteReal = totalVenda - somaTotalInputs;

        if (valorRestanteReal < 0) valorRestanteReal = 0;
          if (Math.abs(valorRestanteReal) < 0.01) {
            e.preventDefault();
            atualizarResumo();
            if (btnFinalizar) {
              btnFinalizar.focus();
            }
          }
        }
    });
  });

    // ========================================== //
  // INPUTS EVENTS COM CAPTURA DE FOCO ATIVO    //
  // ========================================== //
  inputsPagamento.forEach(input => {
    input.addEventListener('focus', function() {
      window.__pdvUltimaFormaFocada = this.dataset.forma;
    });

    input.addEventListener('input', function () {
      const forma = this.dataset.forma;
      let valor = parseFloat(this.value) || 0;
      const restanteSemEsteInput = calcularRestante(this);

      if (forma === 'carteira') {
        const saldoDisponivel = obtenerSaldoCarteira();
        const statusCredito = window.cliente?.status;
        if (statusCredito === 'bloqueado' || saldoDisponivel <= 0) {
          this.value = '';
          atualizarResumo();
          return;
        }
        if (valor > saldoDisponivel) {
          this.value = saldoDisponivel.toFixed(2);
          valor = saldoDisponivel;
        }
      }

      if (forma !== 'dinheiro' && valor > restanteSemEsteInput) {
        this.value = restanteSemEsteInput.toFixed(2);
      }
      atualizarResumo();
    });

    input.addEventListener('blur', function () {
      let valor = this.value.replace(',', '.');
      const n = parseFloat(valor);
      if (isNaN(n) || n <= 0) {
        this.value = '';
        atualizarResumo();
        return;
      }
      this.value = n.toFixed(2);
      atualizarResumo();
    });

    // 🔥 O EVENTO ENTER SINCRONIZADO COM A SUA MATRIZ VISUAL DE VERDADE
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        let valorTexto = this.value.replace(',', '.');
        const n = parseFloat(valorTexto);
        
        if (!isNaN(n) && n > 0) {
          this.value = n.toFixed(2);
        } else {
          this.value = '';
        }

        // 1. Força a atualização da tela para calcular troco/saldo restante
        if (typeof atualizarResumo === 'function') {
          atualizarResumo();
        }

        // 2. Captura o valor restante direto da sua matriz oficial (id="valor-restante")
        // Exatamente igual ao comportamento que você usa na função dos atalhos (dd, cc...)
        const textoRestante = restanteEl.textContent
            .replace(/\./g, '')
            .replace(',', '.')
            .replace(/[^\d.]/g, '');
        
        const valorRestanteReal = parseFloat(textoRestante) || 0;

        // 3. Se o saldo zerou (ou ficou negativo em caso de dinheiro com troco)
        if (valorRestanteReal <= 0) {
          e.preventDefault(); // Impede envios involuntários ou bugs de submit
          
          if (btnFinalizar) {
            // Remove temporariamente o foco do input para evitar conflitos de eventos
            this.blur(); 
            
            // Coloca o foco visual no botão Finalizar Venda
            btnFinalizar.focus();
          }
        }
      }
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'F6') {
      e.preventDefault();
      abrirModalFinalizar();
    }
  });

  // ========================================== //
  // INTEGRADO: FINALIZAR VENDA COM ADIÇÕES     //
  // ========================================== //
  function finalizarVendaPdv() {
      
      const dadosVenda = {
          caixa_id: window.PDV.caixa_id,
          funcionario_id: window.PDV.funcionario_id,
          dataVenda: window.PDV.dataVenda,
          itens: window.carrinho // Envia o array de memória que o localStorage gerenciava
      };

      // Altere para a URL exata da sua rota do Laravel (ex: /pdv/finalizar ou /pdv/venda/store)
      fetch('/pdv/venda/store', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
          },
          body: JSON.stringify(dadosVenda)
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              alert("Venda realizada com sucesso!");

              // 🔥 ONDE O MILAGRE ACONTECE:
              // A venda salvou no banco de dados com segurança. Agora limpamos o navegador 
              // e esvaziamos a tabela visual para o caixa ficar pronto para o próximo cliente.
              if (typeof window.limparPdvLocalStorage === "function") {
                  window.limparPdvLocalStorage();
              }

              // Fecha o modal do Bootstrap se necessário
              const modalEl = document.getElementById('modalFinalizarVenda');
              if (modalEl && typeof bootstrap !== 'undefined') {
                  const modal = bootstrap.Modal.getInstance(modalEl);
                  modal?.hide();
              }

          } else {
              alert("Erro ao finalizar venda: " + (data.message || data.erro));
          }
      })
      .catch(error => {
          console.error('Erro na requisição:', error);
          alert("Erro de comunicação com o servidor.");
      });
  }

  btnFinalizar.addEventListener('click', finalizarVenda);
  btnFinalizar.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') finalizarVenda(e);
  });

    // FINALIZAR VENDA (UMA REQUISIÇÃO INTEGRADA) //
    // ========================================== //
  if (btnFinalizar) {
      async function finalizarVenda(e) {
          e.preventDefault();
          if (btnFinalizar.disabled) return;

          // Captura dos elementos de forma segura
          const inputCliente = document.querySelector('input[name="cliente_id"]') || document.getElementById('input-cliente-id');
          const inputFuncionario = document.querySelector('input[name="operador_id"]') || document.querySelector('input[name="funcionario_id"]') || document.getElementById('input-operador-id');
          const inputCaixa = document.querySelector('input[name="caixa_id"]') || document.getElementById('input-caixa-id');
          const inputData = document.getElementById('dataVenda');

          const cliente_id = inputCliente?.value || null;
          const funcionario_id = inputFuncionario?.value || null;
          const caixa_id = inputCaixa?.value || null;
          const dataVenda = inputData?.value || null;

          if (!funcionario_id || !caixa_id) {
              alert(`Erro local: Operador ou Caixa não identificados.`);
              return;
          }

          const itens = window.carrinho || [];
          if (!itens.length) {
              alert('Adicione itens antes de finalizar');
              return;
          }

          const pagamentos = [];
          inputsPagamento.forEach(input => {
              const valor = parseFloat(input.value) || 0;
              if (valor > 0) {
                  pagamentos.push({ forma: input.dataset.forma, valor });
              }
          });

          if (!pagamentos.length) {
              alert('Informe uma forma de pagamento');
              return;
          }

          // Bloqueia o botão para evitar duplicidade
          const textoOriginalBtn = btnFinalizar.innerHTML;
          btnFinalizar.disabled = true;
          btnFinalizar.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...`;

          try {
              const res = await fetch('/vendas/finalizar', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': token
                  },
                  body: JSON.stringify({ cliente_id, funcionario_id, caixa_id, dataVenda, pagamentos, itens })
              });

              const text = await res.text();
              let dataFinal;
              
              try {
                  dataFinal = JSON.parse(text);
              } catch (errParse) {
                  console.error("Retorno não-JSON recebido:", text);
                  throw new Error("O servidor retornou um erro interno no formato incorreto.");
              }

              if (!res.ok || !dataFinal.success) {
                  throw new Error(dataFinal.erro || "Erro no servidor ao processar venda");
              }

              // LIMPA O CARRINHO E FECHA MODAIS DO PDV ATUAL
               window.carrinho = [];
              // if (typeof modal !== 'undefined' && modal?.hide) {
              //     modal.hide();
              // }

              // 🖨️ MÉTODO TRADICIONAL CORRIGIDO: Abre a URL crua e limpa vinda do banco de dados
             
              // Faz a janela atual virar o cupom imediatamente!
              if (dataFinal.cupom_url) {
                  window.location.href = dataFinal.cupom_url;
                  return; // Interrompe o resto do script para focar apenas no cupom
              }
              // Fallback caso não venha a URL (Garantia de segurança)
              alert('Venda finalizada!');
              window.location.reload();

              // Redirecionamento ou recarga de página obrigatória
              if (dataFinal.redirect_sangria && dataFinal.url) {
                  alert('O limite do caixa foi atingido. Redirecionando para Sangria...');
                  window.location.href = dataFinal.url;
              } else {
                  setTimeout(() => {
                      window.location.reload();
                  }, 300);
              }

          } catch (err) {
              console.error(err);
              alert(err.message);
              btnFinalizar.disabled = false;
              btnFinalizar.innerHTML = textoOriginalBtn;
              btnFinalizar.focus();
          }
      }

      btnFinalizar.addEventListener('click', finalizarVenda);
  }


});

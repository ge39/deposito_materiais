// public/js/produto.js
function previewImage(event, previewId = 'imagemPreview') {
    const input = event.target;
    const preview = document.getElementById(previewId);

    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const nomeInput = document.getElementById('nome');
    const imagemPreview = document.getElementById('imagemPreview');
    const imagemPadrao = '/storage/produtos/4Q6fMmYnfd5CJRJK3kDzvjFSrwiXpaJeAaOcBjz8.png'; // imagem padrão
    let timeout = null;

    nomeInput.addEventListener('input', function () {
        clearTimeout(timeout);
        const nome = this.value.trim();
        if (!nome) {
            limparCampos();
            return;
        }

        timeout = setTimeout(async () => {
            try {
                const response = await fetch(`/produtos/buscar/${encodeURIComponent(nome)}`);
                const data = await response.json();

                if (data.found) {
                    preencherCampos(data);
                } else {
                    limparCampos();
                }
            } catch (error) {
                console.error('Erro ao buscar produto:', error);
                limparCampos();
            }
        }, 300);
    });

    function preencherCampos(produto) {
        document.getElementById('codigo_barras').value = produto.codigo_barras || '';
        document.getElementById('sku').value = produto.sku || '';
        document.getElementById('descricao').value = produto.descricao || '';
        document.getElementById('categoria_id').value = produto.categoria_id || '';
        document.getElementById('fornecedor_id').value = produto.fornecedor_id || '';
        document.getElementById('unidade_medida_id').value = produto.unidade_medida_id || '';
        document.getElementById('marca_id').value = produto.marca_id || '';
        document.getElementById('estoque_minimo').value = produto.estoque_minimo || 0;
        document.getElementById('data_compra').value = produto.data_compra || new Date().toISOString().split('T')[0];
        document.getElementById('validade').value = produto.validade || '';
        document.getElementById('preco_custo').value = produto.preco_custo || 0;
        document.getElementById('preco_venda').value = produto.preco_venda || 0;
        document.getElementById('peso').value = produto.peso || '';
        document.getElementById('largura').value = produto.largura || '';
        document.getElementById('altura').value = produto.altura || '';
        document.getElementById('profundidade').value = produto.profundidade || '';
        document.getElementById('localizacao_estoque').value = produto.localizacao_estoque || '';
        document.getElementById('ativo').checked = produto.ativo === 1;

        // Mostra imagem real ou padrão
        if (produto.imagem) {
            imagemPreview.src = `/storage/${produto.imagem}`;
        } else {
            imagemPreview.src = imagemPadrao;
        }
        imagemPreview.style.display = 'block';
    }

    function limparCampos() {
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('codigo_barras').value = '';
        document.getElementById('sku').value = '';
        document.getElementById('descricao').value = '';
        document.getElementById('categoria_id').value = '';
        document.getElementById('fornecedor_id').value = '';
        document.getElementById('unidade_medida_id').value = '';
        document.getElementById('marca_id').value = '';
        document.getElementById('estoque_minimo').value = 0;
        document.getElementById('data_compra').value = hoje;
        document.getElementById('validade').value = '';
        document.getElementById('preco_custo').value = 0;
        document.getElementById('preco_venda').value = 0;
        document.getElementById('peso').value = '';
        document.getElementById('largura').value = '';
        document.getElementById('altura').value = '';
        document.getElementById('profundidade').value = '';
        document.getElementById('localizacao_estoque').value = '';
        document.getElementById('ativo').checked = true;

        // Sempre mostra a imagem padrão
        imagemPreview.src = imagemPadrao;
        imagemPreview.style.display = 'block';
    }
});



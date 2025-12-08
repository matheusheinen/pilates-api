import { reactive } from 'vue';

// Variável privada para guardar a "promessa" de resposta
let resolverConfirmacao = null;

const estado = reactive({
    ativo: false,
    titulo: '',
    mensagem: '',
    tipo: 'sucesso', // 'sucesso', 'erro', 'confirmacao'
});

export function useAlert() {

    const mostrarSucesso = (msg) => {
        estado.titulo = 'Sucesso!';
        estado.mensagem = msg;
        estado.tipo = 'sucesso';
        estado.ativo = true;
        setTimeout(() => {
            if(estado.tipo === 'sucesso' && estado.ativo) fechar();
        }, 3000);
    };

    const mostrarErro = (msg) => {
        estado.titulo = 'Atenção';
        estado.mensagem = msg;
        estado.tipo = 'erro';
        estado.ativo = true;
    };

    const mostrarConfirmacao = (msg, titulo = 'Tem certeza?') => {
        estado.titulo = titulo;
        estado.mensagem = msg;
        estado.tipo = 'confirmacao';
        estado.ativo = true;

        return new Promise((resolve) => {
            resolverConfirmacao = resolve;
        });
    };

    const confirmar = () => {
        if (resolverConfirmacao) resolverConfirmacao(true);
        fechar();
    };


    const fechar = () => {
        if (estado.tipo === 'confirmacao' && resolverConfirmacao) {
            resolverConfirmacao(false);
            resolverConfirmacao = null;
        }
        estado.ativo = false;
    };

    return {
        estado,
        mostrarSucesso,
        mostrarErro,
        mostrarConfirmacao,
        confirmar,
        fechar
    };
}

# Estilos do site

Os arquivos desta pasta são fontes editáveis: uma declaração por linha, indentação de quatro espaços e blocos de responsividade explícitos.

- `header.css`: única implementação do header, navegação, carrinho e menu de perfil. As classes `site-header` e `site-header__*` são exclusivas desse componente.
- Para mudar a logo, edite `--header-logo-size` e `--header-logo-size-mobile` no início de `header.css`.
- `headergame.css`: importação de compatibilidade que aponta para `header.css`.
- `footer.css`: estilos do rodapé, independentes do header.
- `cinematic.css`: apresentação das páginas e do planeta; não deve conter regras de header.
- `saturn-model.css`: página de prévia do modelo 3D.
- Os demais arquivos mantêm os estilos das páginas e componentes indicados por seus nomes.

Na raiz do projeto, execute `npm run format:css` para formatar todos os CSS próprios e `npm run check:css` para verificar a formatação.

`resources/css/app.css` é a entrada do Vite. Os arquivos em `public/build` são gerados por `npm run build`; edite as fontes, depois regenere o build.

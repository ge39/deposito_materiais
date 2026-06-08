<!-- Para limpar tudo e voltar exatamente ao estado do seu último commit,
  descartando -->

  bashgit reset --hard HEAD

  Esse comando é destrutivo e irreversível.Ele vai apagar todas as modificações que não 
  foram salvas em um commit e deixará o seu código exatamente igual à última versão que estava funcionando

    git clean -fd

  Se houver novos arquivos criados (arquivos não rastreados/untracked) 
  que você também queira deletar de forma definitiva para limpar a pasta, rode em
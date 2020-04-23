# gestaohoras
Plugin para gestão de horas no GLPI

> Já imaginou poder controlar a capacidade e a demanda presentes em sua esteira de serviços? 

O plugin Gestão de Horas veio para apoiar nessa necessidade! 

Com ele, você consegue estabelecer um limite de serviços que os usuários finais podem demandar, sendo que cada serviço solicitado consome um valor da franquia daquele usuário. 

Nessa versão, o plugin tem o seguinte comportamento:
*  Apenas chamados das categorias selecionadas consumirão do saldo;
*  O custo do chamado é contabilizado a partir da soma do campo "Duração", presente dentro das Tarefas do GLPI;
*  O saldo será consumido a partir da soma das durações das tarefas criadas dentro do chamado
   *  Independente do tipo ou do estado da tarefa, caso ela possua uma duração, ela será somada ao "custo" do chamado
   *  Somente chamados FECHADOS serão selecionados. Tarefas de chamados ainda em aberto serão desconsiderados
*  O custo do chamado será debitado de todos os grupos que estiverem atribuídos como requerentes

Para começar a usar, siga os passos abaixo:
*  Após instalação, crie o saldo dos grupos em Administração > Gestão de Horas
  *  Saldo padrão é o "salário" dos grupos, ou seja, é o limite que será renovado mensalmente em cada grupo.
*  Em Administração > Gestão de Horas > Configuração de Categorias, selecione quais categorias consumirão do saldo dos grupos
*  Em Configurar > Ações Automáticas, habilite a ação DebitoDeHoras para executar a cada 1 minuto. Ela é responsável por calcular os custos dos chamados e lançar os débitos nos grupos.
* Em Configurar > Ações Automáticas, habilite a ação RecarregarSaldos para executar todo início de mês. Ela é responsável por recarregar os saldos mensalmente.

Para mais informações, leia o artigo abaixo:
https://link.medium.com/dIJWx5P6T5

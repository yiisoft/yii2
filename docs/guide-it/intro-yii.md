Cos'è Yii
===========

Yii è un framework PHP ad alte prestazioni, basato su component, per lo sviluppo veloce di applicazioni web moderne.
Il nome Yii (pronunciato `Yii` o `[ji:]`) significa "semplice ed evoutivo" in cinese. Può anche essere visto come un acronimo di **Yes It Iss** (si lo è)!


Qual'è il migliore impiego di Yii?
----------------------------------

Yii è un framework di programmazione, il che significa che può essere utilizzato per sviluppare ogni
tipo di appicazione con PHP. Grazie alla sua architettura basata sui componenti e al suo avanzato
supporto della cache, è particolarmente adeguato per lo sviluppo di applicazioni su larga scala quali
portali, forum, gestori di contenuti (CMS), progetti di e-commerce, servizi web RESTful, e così via.


Come si pone Yii rispetto ad altri framework?
---------------------------------------------

Se hai già familiarità con altri framework potrai apprezzare questi punti in comune:
- Come la maggior parte dei framework, Yii implementa il paradigma di sviluppo MVC (Model-View-Controller) e 
  promuove l'organizzazione del codice secondo quelle regole.
- Yii usa la filosofia secondo cui il codice dovrebbe essere semplice ed elegante. Yii non cercherà mai di 
  ridisegnare le cose solo per seguire dei pattern di sviluppo.
- Yii è un framework completo in grado di fornire diverse funzionalità testate e pronte all'uso: costruttori di
  query ed ActiveRecord sia per i database relazionali che NoSQL; supporto allo sviluppo di applicazioni RESTful;
  supporto di caching a diversi livelli; e altro.
- Yii è estremamente estensibile. Puoi pesonalizzare o sostituire quasi ogni singolo pezzo del codice base. Puoi anche
  sfuttare la solida architettura delle estensioni di Yii per usare o sviluppare estensioni ridistribuibili.
- Le prestazioni elevate sono sempre il focus primario di Yii.

Yii non è frutto di un uomo solo, ma è supportato da un [folto gruppo di sviluppatori][about_yii], così come da una numerosa
comunità di professionisti che contribuiscono costantemente allo sviluppo. Il gruppo di sviluppatori tiene sempre 
sott'occhio le ultime tendenze e tecnologie di sviluppo web, sulle pratiche ottimali e funzionalità degli altri
framework e progetti. Le peculiarità più rilevanti che si trovano altrove sono regolarmente incorporate nel
codice principale del framework, e rese disponibili tramite semplici ed eleganti interfacce.

[about_yii]: https://www.yiiframework.com/about/

Versioni di Yii
---------------

Yii al momento ha due versioni principali disponibili: 1.1 e 2.0. La versione 1.1 è la vecchia generazione ed è ora in 
uno stato di manutenzione. La versione 2.0 è una riscrittura completa di Yii che utilizza le ultime tecnologie e protocolli, 
inclusi Composer, PSR, namespace, trait, e così via. La versione 2.0 rappresenta l'attuale generazione del framework e 
riceverà i maggiori sforzi di sviluppo nei prossimi anni.
Questa guida è focalizzata principalmente sulla versione 2.0.


Richieste e requisiti di sistema
---------------------------------

Yii 2.0 richiede PHP 5.4.0 o successivo. Puoi trovare maggiori dettagli sulle richieste delle singole funzionalità
eseguendo lo script di verifica requisiti incluso in ogni versione di Yii.

L'uso di Yii richiede una conoscenza base della programmazione ad oggetti (OOP), dato che Yii è un framework puramente OOP.
Yii 2.0 fa uso delle più recenti funzionalità di PHP, come i [namespace](https://www.php.net/manual/it/language.namespaces.php) e 
[trait](https://www.php.net/manual/it/language.oop5.traits.php). La compresione di questi concetti ti aiuterà a semplificare
l'uso di Yii 2.0.

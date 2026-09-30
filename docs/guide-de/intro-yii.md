Was ist Yii?
============

Yii ist ein leistungsstarkes, komponentenbasiertes PHP-Framework zur schnellen Entwicklung moderner Webanwendungen.
Der Name Yii (ausgesprochen `Ji` oder `[ji:]`) bedeutet im Chinesischen "einfach und evolutionär". Er kann außerdem
als Akronym für **Yes It Is!** (zu Deutsch **Ja, ist es!**) verstanden werden.


Wofür ist Yii am besten geeignet?
---------------------------------

Yii ist universell einsetzbar: Mit ihm lassen sich Webanwendungen aller Art entwickeln. Dank seiner komponentenbasierten
Architektur und seiner ausgefeilten Caching-Unterstützung eignet es sich besonders für umfangreiche Anwendungen wie
Portale, Foren, Content-Management-Systeme (CMS), E-Commerce-Projekte, RESTful Web Services und vieles mehr.


Wie unterscheidet sich Yii von anderen Frameworks?
--------------------------------------------------

Wenn Sie bereits mit einem anderen Framework vertraut sind, wird es Sie vermutlich interessieren, wie Yii im Vergleich
dazu abschneidet:

- Wie die meisten PHP-Frameworks implementiert Yii das Architekturmuster MVC (Model-View-Controller) und fördert eine
  Organisation des Codes, die auf diesem Muster basiert.
- Yii vertritt die Philosophie, dass Code auf eine einfache und dennoch elegante Weise geschrieben werden sollte.
  Yii wird nie versuchen, Strukturen zu verkomplizieren, nur um ein bestimmtes Entwurfsmuster strikt einzuhalten.
- Yii ist ein Full-Stack-Framework: Seine Komponenten decken alle Schichten einer Anwendung ab und bieten viele
  bewährte und sofort einsatzbereite Funktionen, darunter Query Builder und ActiveRecord für relationale und
  NoSQL-Datenbanken, Unterstützung für die Entwicklung von RESTful APIs, mehrstufiges Caching und mehr.
- Yii ist sehr gut erweiterbar. Sie können fast jeden Teil des Kerncodes anpassen oder ersetzen. Sie können außerdem
  die solide Erweiterungsarchitektur von Yii nutzen, um weiterverteilbare Erweiterungen einzusetzen oder zu entwickeln.
- Hohe Performance ist immer ein vorrangiges Ziel von Yii.

Yii ist keine One-Man-Show, sondern wird von einem [starken Kern-Entwicklerteam](https://www.yiiframework.com/team/)
sowie einer [großen Community](https://github.com/yiisoft/yii2/graphs/contributors) von Fachleuten unterstützt, die
ständig zur Entwicklung von Yii beitragen. Das Yii-Entwicklerteam behält die neuesten Webentwicklungstrends sowie die
Best Practices und Funktionen im Auge, die in anderen Frameworks und Projekten zu finden sind. Die relevantesten
Best Practices und Funktionen, die anderswo zu finden sind, werden regelmäßig in das Kernframework integriert und über
einfache und elegante Schnittstellen zugänglich gemacht.


Yii-Versionen
-------------

Yii steht derzeit in zwei Major-Versionen zur Verfügung: 1.1 und 2.0. Version 1.1 ist die alte Generation und befindet
sich nun im Wartungsmodus. Version 2.0 ist eine komplette Neufassung von Yii, die die neuesten Technologien und
Protokolle aufgreift, einschließlich Composer, PSR, Namespaces, Traits usw. Version 2.0 ist die aktuelle Generation
des Frameworks und wird in den nächsten Jahren der Hauptfokus der Entwicklung sein.
In diesem Handbuch geht es hauptsächlich um die Version 2.0.

> Info: Mit Yii3 ist inzwischen auch die nächste Generation des Frameworks verfügbar. Sie wird in diesem Handbuch
> nicht behandelt. Weitere Informationen finden Sie im [Handbuch zu Yii3](https://yiisoft.github.io/docs/guide/).


Anforderungen und Voraussetzungen
---------------------------------

Yii 2.0 erfordert PHP 7.4.0 oder höher und läuft am besten mit der neuesten Version von PHP. Detailliertere
Informationen über die Anforderungen einzelner Funktionen erhalten Sie, indem Sie den "Requirement Checker" ausführen,
der in jedem Yii-Release enthalten ist.

Die Verwendung von Yii erfordert Grundkenntnisse in der objektorientierten Programmierung (OOP), da es sich bei Yii um
ein rein OOP-basiertes Framework handelt. Yii 2.0 nutzt auch die neuesten Funktionen von PHP, wie z. B.
[Namespaces](https://www.php.net/manual/de/language.namespaces.php) und
[Traits](https://www.php.net/manual/de/language.oop5.traits.php). Das Verständnis dieser Konzepte wird Ihnen helfen,
sich leichter in Yii 2.0 zurechtzufinden.

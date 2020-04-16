Yii는 무엇인가?
===========

Yii is a high performance, component-based PHP framework for rapidly developing modern Web applications.
The name Yii (pronounced `Yee` or `[ji:]`) means "simple and evolutionary" in Chinese. It can also
be thought of as an acronym for **Yes It Is**!

Yii는 최신 웹 응용 프로그램을 빠르게 개발하기 위한 고성능 컴포넌트(구성요소) 기반 PHP 프레임워크입니다.
Yii( 'Yee'또는 `[ji :]`로 발음)는 중국어로 "단순하고 진화적"인 것을 의미합니다. 또한 
** Yes It Is **의 약자로 생각할 수 있습니다. 


Yii는 어디에 가장 잘 쓰일수 있는가?
---------------------

Yii is a generic Web programming framework, meaning that it can be used for developing all kinds
of Web applications using PHP. Because of its component-based architecture and sophisticated caching
support, it is especially suitable for developing large-scale applications such as portals, forums, content
management systems (CMS), e-commerce projects, RESTful Web services, and so on.

Yii는 일반적인 웹 프로그래밍 프레임워크로 PHP를 사용하는 모든 종류의 웹 응용프로그램 개발에 사용될 수 있습니다. 
구성 요소 기반 아키텍처와 정교한 캐싱으로 인해, 포털, 포럼, 문서관리시스템(CMS), 전자 상거래 프로젝트, 
RESTful 웹 서비스 등과 같은 대규모 애플리케이션 개발에 특히 적합합니다. 

다른 프레임워크와 비교해서 Yii는 어떠한가? 
-------------------------------------------

If you're already familiar with another framework, you may appreciate knowing how Yii compares:
다른 프레임워크에 이미 익숙한 경우, Yii가 어떻게 비교하는지 알고 있으면 좋을 것입니다.

- Like most PHP frameworks, Yii implements the MVC (Model-View-Controller) architectural pattern and promotes code
  organization based on that pattern. 
  
- 대부분의 PHP 프레임워크와 마찬가지로 Yii는 MVC (Model-View-Controller) 아키텍처 패턴을 구현하고, 그 패턴을 기반하여 
  코드 구성을 촉진합니다.

- Yii takes the philosophy that code should be written in a simple yet elegant way. Yii will never try to
  over-design things mainly for the purpose of strictly following some design pattern.
  Yii는 코드는 단순하면서도 우아한 방식으로 작성되어야한다는 철학을 가지고 있습니다. Yii는 어떤 디자인 패턴을 엄격하게 따르기 위해 주로 과도하게 디자인하려고하지 않습니다.
  
- Yii is a full-stack framework providing many proven and ready-to-use features: query builders
  and ActiveRecord for both relational and NoSQL databases; RESTful API development support; multi-tier
  caching support; and more.

- Yii는 입증된 바로 사용 가능한 많은 기능을 제공하는 풀스택 프레임워크입니다. 관계형 데이터베이스와 
  NoSQL 데이터베이스 모두에 대한 쿼리 작성기 및 ActiveRecord, RESTful API 개발 지원, 다 계층 캐싱 지원 등을 지원합니다. 
  
- Yii is extremely extensible. You can customize or replace nearly every piece of the core's code. You can also
  take advantage of Yii's solid extension architecture to use or develop redistributable extensions.

- Yii는 엄청나게 확장 가능합니다. 코어 코드의 거의 모든 부분을 사용자 정의하거나 바꿀 수 있습니다. 
  또한 Yii의 견고한 확장 아키텍처를 활용하여 재배포 가능한 확장을 사용하거나 개발할 수도 있습니다.
  
- High performance is always a primary goal of Yii.
- 고성능은 항상 Yii의 주요 목표입니다.


Yii is not a one-man show, it is backed up by a [strong core developer team](http://www.yiiframework.com/team/), as well as a large community
of professionals constantly contributing to Yii's development. The Yii developer team
keeps a close eye on the latest Web development trends and on the best practices and features
found in other frameworks and projects. The most relevant best practices and features found elsewhere are regularly incorporated into the core framework and exposed
via simple and elegant interfaces.

Yii는 한 사람의 공연이 아니며, [강력한 핵심 개발자 팀](http://www.yiiframework.com/team/)과 Yii의 개발에 지속적으로 기여하는 
대규모 전문가 커뮤니티에 의해 지원되고 있습니다. Yii 개발자 팀은 최신 웹 개발 트렌드와 다른 프레임 워크 및 프로젝트의 모범 사례 및 기능을 면밀히 검토합니다. 다른 곳에서 찾을 수있는 가장 관련성이 높은 모범 사례 및 기능은 정기적으로 핵심 프레임 워크에 통합되고 
단순하고 우아한 인터페이스를 통해 노출됩니다.


Yii Versions
Yii의 버전들
------------


Yii currently has two major versions available: 1.1 and 2.0. Version 1.1 is the old generation and is now in maintenance mode. Version 2.0 is a complete rewrite of Yii, adopting the latest
technologies and protocols, including Composer, PSR, namespaces, traits, and so forth. Version 2.0 represents the current
generation of the framework and will receive the main development efforts over the next few years.
This guide is mainly about version 2.0.

Yii에는 현재 1.1과 2.0의 두 가지 주요 버전이 있습니다. 버전 1.1은 이전 세대이며 현재 유지 보수 모드입니다. 버전 2.0은 Composer, PSR, 네임스페이스,  트레이트(Trate) 등을 포함한 최신 기술과 프로토콜을 채택하여 Yii를 완전히 다시 작성한 것입니다. 
버전 2.0은 프레임워크의 현재 세대를 나타내며 향후 몇 년 동안 주력으로 개발을 할 것입니다. 이 안내서는 주로 버전 2.0에 관한 것입니다.


Requirements and Prerequisites
요구 사항 및 전제 조건
------------------------------

Yii 2.0 requires PHP 5.4.0 or above and runs best with the latest version of PHP 7. You can find more detailed
requirements for individual features by running the requirement checker included in every Yii release.

Yii 2.0에는 PHP 5.4.0 이상이 필요하며 최신 버전의 PHP 7에서 가장 잘 실행됩니다. 
모든 Yii 릴리스에 포함된 요구 사항 검사기를 실행하여 개별 기능에 대한 자세한 요구 사항을 찾을 수 있습니다.

Using Yii requires basic knowledge of object-oriented programming (OOP), as Yii is a pure OOP-based framework.
Yii 2.0 also makes use of the latest features of PHP, such as [namespaces](https://secure.php.net/manual/en/language.namespaces.php)
and [traits](https://secure.php.net/manual/en/language.oop5.traits.php). Understanding these concepts will help
you more easily pick up Yii 2.0.

Yii는 순수한 객체 지향 프로그래밍(OOP) 기반 프레임 워크이므로 Yii를 사용하려면 객체 지향 프로그래밍(OOP)에 대한 기본 지식이 필요합니다. 
Yii 2.0은 네임스페이스 및 특성과 같은 PHP의 최신 기능도 사용합니다. 이러한 개념을 이해하면 Yii 2.0을보다 쉽게 ​​선택할 수 있습니다.


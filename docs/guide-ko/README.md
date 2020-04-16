Yii 2.0에 대한 결정적인 가이드 
===============================

이 학습서는 [Yii 문서 규약](http://www.yiiframework.com/doc/terms/)에 따라 배포됩니다.

판권소유.

2014(c) Yii Software LLC.


소개
------------

* [Yii 소개](intro-yii.md)
* [버전 1.1에서 업그레이드](intro-upgrade-from-v1.md)


시작하기
---------------

* [알아야 할 사항](start-prerequisites.md)
* [Yii 설치하기](start-installation.md)
* [응용 프로그램 실행하기](start-workflow.md)
* [첫 인사](start-hello.md)
* [양식(폼) 작업](start-forms.md)
* [데이터베이스 작업](start-databases.md)
* [Gii로 코드 생성](start-gii.md)
* [더 나아가기](start-looking-ahead.md)


응용 프로그램 구조
---------------------

* [응용 프로그램 구조 개요](structure-overview.md)
* [도입부 스크립트](structure-entry-scripts.md)
* [응용 프로그램](structure-applications.md)
* [응용 프로그램 구성 요소](structure-application-components.md)
* [컨트롤러](structure-controllers.md)
* [모델](structure-models.md)
* [뷰](structure-views.md)
* [모듈](structure-modules.md)
* [필터](structure-filters.md)
* [위젯](structure-widgets.md)
* [에셋](structure-assets.md)
* [확장](structure-extensions.md)


요청 처리하기
-----------------

* [요청 처리 개요](runtime-overview.md)
* [부트스트랩(시작)](runtime-bootstrapping.md)
* [라우팅 및 URL 생성](runtime-routing.md)
* [요청](runtime-requests.md)
* [응답](runtime-responses.md)
* [세션 및 쿠키](runtime-sessions-cookies.md)
* [오류 처리](runtime-handling-errors.md)
* [로깅](runtime-logging.md)


주요 개념 
------------

* [구성 요소](concept-components.md)
* [속성](concept-properties.md)
* [이벤트](concept-events.md)
* [행동(Behaviors)](concept-behaviors.md)
* [구성](concept-configurations.md)
* [별명](concept-aliases.md)
* [클래스 자동 로딩](concept-autoloading.md)
* [서비스 로케이터(Service Locator)](concept-service-locator.md)
* [의존성 주입 컨테이너](concept-di-container.md)


데이터베이스 작업
----------------------

* [데이터베이스 접속 개체(Database access object)](db-dao.md): 데이터베이스에 연결, 기본 쿼리, 트랜잭션 및 스키마 조작 
* [쿼리 작성기](db-query-builder.md): 간단한 추상화 계층을 사용하여 데이터베이스 쿼리 
* [활성 레코드](db-active-record.md): 활성 레코드 ORM, 레코드 검색 및 조작, 관계 정의 
* [마이그레이션](db-migrations.md): 팀 개발 환경에서 데이터베이스에 버전 제어 적용 
* [스핑크스(Sphinx)](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [레디스(Redis)](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


사용자로부터 데이터 얻기
-----------------------

* [양식 만들기](input-forms.md)
* [입력 확인](input-validation.md)
* [파일 업로드](input-file-upload.md)
* [테이블 형식의 입력 수집](input-tabular-input.md)
* [여러 모델에 대한 데이터 가져 오기](input-multiple-models.md)
* [클라이언트 측에서 ActiveForm 확장](input-form-javascript.md)


데이터 표시
---------------

* [데이터 형식화(Formatting)](output-formatting.md)
* [쪽수 매기기](output-pagination.md)
* [정렬](output-sorting.md)
* [데이터 제공자](output-data-providers.md)
* [데이터 위젯](output-data-widgets.md)
* [클라이언트 스크립트 작업](output-client-scripts.md)
* [테마](output-theming.md)


보안
--------

* [보안 개요](security-overview.md)
* [입증(authentication)](security-authentication.md)
* [권한 부여(authorization)](security-authorization.md)
* [비밀번호 작업](security-passwords.md)
* [암호화](security-cryptography.md)
* [인증 클라이언트](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [모범 사례](security-best-practices.md)


캐싱
-------

* [캐싱 개요](caching-overview.md)
* [데이터 캐싱](caching-data.md)
* [조각 캐싱](caching-fragment.md)
* [페이지 캐싱](caching-page.md)
* [HTTP 캐싱](caching-http.md)


RESTful 웹 서비스
--------------------

* [빠른 시작](rest-quick-start.md)
* [자원(Resources)](rest-resources.md)
* [컨트롤러(Controllers)](rest-controllers.md)
* [라우팅](rest-routing.md)
* [응답 형식](rest-response-formatting.md)
* [입증](rest-authentication.md)
* [속도 제한](rest-rate-limiting.md)
* [버전 관리](rest-versioning.md)
* [오류 처리](rest-error-handling.md)


개발 도구
-----------------

* [디버그 툴바 및 디버거](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Gii를 사용하여 코드 생성](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [API 문서 생성](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


테스트하기 
-------

* [테스트 개요](test-overview.md)
* [테스트 환경 설정](test-environment-setup.md)
* [단위 테스트](test-unit.md)
* [기능 테스트](test-functional.md)
* [합격 시험](test-acceptance.md)
* [Fixtures](test-fixtures.md)


특별 주제
--------------

* [고급 프로젝트 템플릿](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [처음부터 응용프로그램 개발](tutorial-start-from-scratch.md)
* [콘솔 명령](tutorial-console.md)
* [핵심 검증자](tutorial-core-validators.md)
* [도커](tutorial-docker.md)
* [국제화](tutorial-i18n.md)
* [메일링](tutorial-mailing.md)
* [성능 조정](tutorial-performance-tuning.md)
* [공유 호스팅 환경](tutorial-shared-hosting.md)
* [템플릿 엔진](tutorial-template-engines.md)
* [제3자(타사) 코드와 함께 작업하기](tutorial-yii-integration.md)
* [Yii를 마이크로 프레임 워크로 사용](tutorial-yii-as-micro-framework.md)


위젯
-------

* [격자보기(Grid view)](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [목록보기](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [자세한 내용](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [액티브 폼(ActiveForm)](https://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [메뉴](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap 위젯(Widgets)](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [jQuery UI 위젯](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


헬퍼(Helpers)
-------

* [헬퍼 개요](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)


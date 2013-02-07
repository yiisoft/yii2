- db
	* pgsql, sql server, oracle, db2 drivers
	* unit tests on different DB drivers
	* document-based (should allow storage-specific methods additionally to generic ones)
	  * mongodb (put it under framework/db/mongodb)
	* key-value-based (should allow storage-specific methods additionally to generic ones)
	  * redis (put it under framework/db/redis or perhaps framework/caching?)
- base
	* TwigViewRenderer
	* SmartyViewRenderer
- logging
	* WebTarget (TBD after web is in place): should consider using javascript and make it into a toolbar
	* ProfileTarget (TBD after web is in place): should consider using javascript and make it into a toolbar
	* unit tests
- caching
	* backend-specific unit tests
- validators
	* FileValidator: depends on CUploadedFile
	* CaptchaValidator: depends on CaptchaAction
	* DateValidator: should we use CDateTimeParser, or simply use strtotime()?
	* CompareValidator::clientValidateAttribute(): depends on CHtml::activeId()

---

- base
	* module
	  - Module should be able to define its own configuration including routes. Application should be able to overwrite it.
	* application
	* security
- built-in console commands
	+ api doc builder
		* support for markdown syntax
		* support for [[name]]
		* consider to be released as a separate tool for user app docs
- i18n
	* consider using PHP built-in support and data
	* message translations, choice format
	* formatting: number and date
	* parsing??
	* make dates/date patterns uniform application-wide including JUI, formats etc.
- helpers
	* array
	* image
	* string
	* file
- web: TBD
	* get/setFlash() should be moved to session component
	* support optional parameter in URL patterns
	* Response object.
	* ErrorAction
- gii
    * move generation API out of gii, provide yiic commands to use it. Use same templates for gii/yiic.
	* i18n variant of templates
	* allow to generate module-specific CRUD
- markup and HTML helpers
    * use HTML5 instead of XHTML
- assets
    * ability to manage scripts order (store these in a vector?)
	* http://ryanbigg.com/guides/asset_pipeline.html, http://guides.rubyonrails.org/asset_pipeline.html, use content hash instead of mtime + directory hash.
- Requirement checker
- Optional configurable input filtering in request
- widgets
    * if we're going to supply default ones, these should generate really unique IDs. This will solve a lot of AJAX-nesting problems.
- Make sure type hinting is used when components are passed to methods
- Decouple controller from application (by passing web application instance to controller and if not passed, using Yii::app())?

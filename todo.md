- logging
	* WebTarget
	* ProfileTarget
- base
	* module
	  - Module should be able to define its own configuration including routes. Application should be able to overwrite it.
	* application
	* security
- validators
	* type conversion rules
	* CompareValidator::clientValidateAttribute(): search for "CHtml::activeId"
	* FileValidator, UniqueValidator, ExistValidator, DateValidator: TBD
	* when getting errors from getErrors it will be good to have which validator (at least type) failed exactly.
- built-in console commands
	+ api doc builder
		* support for markdown syntax
		* support for [[name]]
		* consider to be released as a separate tool for user app docs
- caching
	* a way to invalidate/clear cached data
	* a command to clear cached data
- db
	* pgsql, sql server, oracle, db2 drivers
	  * write a guide on creating own schema definitions
	* document-based (should allow storage-specific methods additionally to generic ones)
	  * mongodb
	* key-value-based (should allow storage-specific methods additionally to generic ones)
	  * redis
	  * memcachedb
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

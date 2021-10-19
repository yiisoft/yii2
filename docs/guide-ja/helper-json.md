Json �w���p
===========

Json �w���p�� JSON ���G���R�[�h����уf�R�[�h�����A�̐ÓI���\�b�h��񋟂��܂��B
`[[yii\helpers\Json::encode()]]` ���\�b�h�̓G���R�[�h�E�G���[���������܂����A
 `[[yii\web\JsExpression]]` �I�u�W�F�N�g�̌`���ŕ\�����ꂽ JavaScript �̎��̓G���R�[�h���܂���B
����ł̓G���R�[�h�� `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` �̃I�v�V�����ōs���܂��B
�ڍׂɂ��Ă� [PHP:json_encode](https://www.php.net/manual/ja/function.json-encode.php) ���Q�Ƃ��ĉ������B

## ���`�o�� <span id="pretty-print"></span>

����ł� `[[yii\helpers\Json::encode()]]` ���\�b�h�͐��`����Ă��Ȃ� JSON (���Ȃ킿�󔒖����̂���) ���o�͂��܂��B
�l�ԂɂƂ��ēǂ݂₷�����̂ɂ��邽�߂ɁA�u���`�o�� pretty printing�v�� ON �ɂ��邱�Ƃ��o���܂��B

> Note: ���`�o�͂͊J�����̃f�o�b�O�ɂ͖𗧂ł��傤���A���i���ł͐�������܂���B

�C���X�^���X���Ƃɐ��`�o�͂�L���ɂ��邽�߂ɂ̓I�v�V�������w�肷�邱�Ƃ��o���܂��B���Ȃ킿 :

```php
$data = ['a' => 1, 'b' => 2];
$json = yii\helpers\Json::encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```
JSON �w���p�̐��`�o�͂��O���[�o���ɗL���ɂ��邱�Ƃ��o���܂��B�Ⴆ�΁A�ݒ�t�@�C���� index.php �̒��� :
```php
yii\helpers\Json::$prettyPrint = YII_DEBUG; // �f�o�b�O�E���[�h�ł͐��`�o�͂��g�p
```

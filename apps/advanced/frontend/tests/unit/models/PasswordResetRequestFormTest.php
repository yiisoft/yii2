<?php

namespace frontend\tests\unit\models;

use Yii;
use frontend\tests\unit\DbTestCase;
use frontend\models\PasswordResetRequestForm;
use common\tests\fixtures\UserFixture;
use common\models\User;

class PasswordResetRequestFormTest extends DbTestCase
{
    use \Codeception\Specify;

    protected function setUp()
    {
        parent::setUp();
        Yii::$app->mail->fileTransportCallback = function ($mailer, $message) {
            return 'testing_message.eml';
        };
    }

    protected function tearDown()
    {
        @unlink($this->getMessageFile());
        parent::tearDown();
    }

    public function testSendEmailWrongUser()
    {
        $this->specify('no user with such email, message should not be send', function () {
            $model = new PasswordResetRequestForm();
            $model->email = 'not-existing-email@example.com';

            expect('email not send', $model->sendEmail())->false();
        });

        $this->specify('user is not active, message should not be send', function () {
            $model = new PasswordResetRequestForm();
            $model->email = $this->user[1]['email'];

            expect('email not send', $model->sendEmail())->false();
        });
    }

    public function testSendEmailCorrectUser()
    {
        $model = new PasswordResetRequestForm();
        $model->email = $this->user[0]['email'];
        $user = User::find(['password_reset_token' => $this->user[0]['password_reset_token']]);

        expect('email sent', $model->sendEmail())->true();
        expect('user has valid token', $user->password_reset_token)->notNull();

        $this->specify('message has correct format', function () use ($model) {
            expect('message file exists', file_exists($this->getMessageFile()))->true();

            $message = file_get_contents($this->getMessageFile());
            expect('message "from" is correct', $message)->contains(Yii::$app->params['supportEmail']);
            expect('message "to" is correct', $message)->contains($model->email);
        });
    }

    public function fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => '@frontend/tests/unit/fixtures/data/user.php'
            ],
        ];
    }

    private function getMessageFile()
    {
        return Yii::getAlias(Yii::$app->mail->fileTransportPath) . '/testing_message.eml';
    }
}

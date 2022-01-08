$in = password_hash(Yii::$app->params['salt'].$str.$salt, PASSWORD_BCRYPT, ['cost' => 12]); 

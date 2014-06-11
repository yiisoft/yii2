<?php
/**
 * @var yii\debug\panels\RequestPanel $panel
 */

use yii\bootstrap\Tabs;

echo "<h1>Request</h1>";

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Parameters',
            'content' => $this->render('table', ['caption' => 'Routing', 'values' => ['Route' => $panel->data['route'], 'Action' => $panel->data['action'], 'Parameters' => $panel->data['actionParams']]])
                . $this->render('table', ['caption' => '$_GET', 'values' => $panel->data['GET']])
                . $this->render('table', ['caption' => '$_POST', 'values' => $panel->data['POST']])
                . $this->render('table', ['caption' => '$_FILES', 'values' => $panel->data['FILES']])
                . $this->render('table', ['caption' => '$_COOKIE', 'values' => $panel->data['COOKIE']])
                . $this->render('table', ['caption' => 'Request Body', 'values' => $panel->data['requestBody']]),
            'active' => true,
        ],
        [
            'label' => 'Headers',
            'content' => $this->render('table', ['caption' => 'Request Headers', 'values' => $panel->data['requestHeaders']])
                . $this->render('table', ['caption' => 'Response Headers', 'values' => $panel->data['responseHeaders']])
        ],
        [
            'label' => 'Session',
            'content' => $this->render('table', ['caption' => '$_SESSION', 'values' => $panel->data['SESSION']])
                . $this->render('table', ['caption' => 'Flashes', 'values' => $panel->data['flashes']])
        ],
        [
            'label' => '$_SERVER',
            'content' => $this->render('table', ['caption' => '$_SERVER', 'values' => $panel->data['SERVER']]),
        ],
    ],
]);

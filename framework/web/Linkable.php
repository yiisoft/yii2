<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * Linkable is the interface that should be implemented by classes that typically represent locatable resources.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Linkable
{
    /**
     * Returns a list of links.
     *
     * Each link is either a URI or a [[Link]] object. The return value of this method should
     * be an array whose keys are the relation names and values the corresponding links.
     *
     * If a relation name corresponds to multiple links, use an array to represent them.
     *
     * For example,
     *
     * ```
     * [
     *     'self' => 'https://example.com/users/1',
     *     'friends' => [
     *         'https://example.com/users/2',
     *         'https://example.com/users/3',
     *     ],
     *     'manager' => $managerLink, // $managerLink is a Link object
     * ]
     * ```
     *
     * @return array the links
     */
    public function getLinks();
}

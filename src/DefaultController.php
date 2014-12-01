<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Viking\Content\Page;

/**
 * Class DefaultController
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking
 */
class DefaultController {

    public function pageAction(Request $request, Page $page) {
        return array(
            "page" => $page
        );
    }
} 
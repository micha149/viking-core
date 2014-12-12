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
use Viking\Content\Page;
use Viking\Content\PageRepository;
use Viking\Controller\Controller;

/**
 * Class DefaultController
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking
 */
class DefaultController extends Controller {

    public function pageAction(Request $request, Page $page) {

        /** @var $repository PageRepository */
        $repository = $this->get('content.page_repository');

        return array(
            "page" => $page,
            "pages" => $repository->findRootPages()
        );
    }
} 
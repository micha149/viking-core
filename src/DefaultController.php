<?php
/**
 * Created by PhpStorm.
 * User: micha149
 * Date: 18.11.14
 * Time: 21:04
 */

namespace Viking;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Viking\Content\Page;

class DefaultController {

    public function pageAction(Request $request, Page $page) {
        return array(
            "page" => $page
        );
    }
} 
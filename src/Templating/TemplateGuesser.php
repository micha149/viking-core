<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking\Templating;

/**
 * Class TemplateGuesser
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Templating
 */
class TemplateGuesser implements TemplateGuesserInterface
{

    /**
     * @var string
     */
    private $templateFolder;

    /**
     * @param string $templateFolder Path to folder with templates
     */
    public function __construct($templateFolder)
    {
        $this->templateFolder = $templateFolder;
    }

    public function guessTemplateName()
    {
        return 'default.html.twig';
    }
}
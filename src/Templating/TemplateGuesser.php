<?php
/**
 * Created by PhpStorm.
 * User: micha149
 * Date: 20.11.14
 * Time: 11:56
 */

namespace Viking\Templating;


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
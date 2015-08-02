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
use Symfony\Component\Finder\Finder;

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

    public function guessTemplateName($pageType)
    {
        $finder = new Finder();

        $finder->in($this->templateFolder)->name($pageType . '.*');
        $first = reset(iterator_to_array($finder->getIterator()));

        if ($first) {
            return $first->getBaseName();
        }

        return 'default.html.twig';
    }
}
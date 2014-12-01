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
 * Interface TemplateGuesserInterface
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Templating
 */
interface TemplateGuesserInterface {

    public function guessTemplateName();

} 
<?php namespace Sanatorium\Shop\Presenters\Pagination;
/**
 * BootstrapThreeLitePresenter
 *
 * Pagination presenter made for lite version
 * of catalogue pagination. 
 *
 * This pagination presenter is not tied specifically
 * to Bootstrap, nor fishcat/shop and can be used
 * just about anywhere it's needed.
 *
 * @package    Platform
 * @version    2.0.0
 * @author     Sanatorium
 * @license    WTFPL2
 * @copyright  (c) 2015, Sanatorium
 * @link       http://sanatorium.ninja
 */

use Illuminate\Pagination\BootstrapThreePresenter;

class BootstrapThreeCustomPresenter extends BootstrapThreePresenter 
{
    public function render($classes = null)
    {
        if ($this->hasPages())
        {
            return sprintf(
                '<ul class="%s">%s %s %s</ul>',
                $classes,
                $this->getPreviousButton( trans('pagination.previous_short') ),
                $this->getLinks(),
                $this->getNextButton( trans('pagination.next_short') )
            );
        }

        return '';
    }

}



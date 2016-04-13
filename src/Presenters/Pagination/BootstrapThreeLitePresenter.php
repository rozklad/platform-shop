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

class BootstrapThreeLitePresenter extends BootstrapThreePresenter {

    public function render($classes = null)
    {
        if ($this->hasPages())
        {
            return sprintf(
                '<ul class="%s">%s %s %s</ul>',
                $classes,
                $this->getPreviousButton( trans('pagination.previous') ),
                $this->getLinks(),
                $this->getNextButton( trans('pagination.next') )
            );
        }

        return '';
    }

    /**
	 * Get the previous page pagination element.
	 *
	 * @param  string  $text
	 * @return string
	 */
	public function getPreviousButton($text = '&laquo;')
	{
		// If the current page is less than or equal to one, it means we can't go any
		// further back in the pages, so we will render a disabled previous button
		// when that is the case. Otherwise, we will give it an active "status".
		if ($this->paginator->currentPage() <= 1)
		{
			return null;
		}

		$url = $this->paginator->url(
			$this->paginator->currentPage() - 1
		);

		return $this->getPageLinkWrapper($url, $text, 'prev');
	}

	/**
	 * Get the next page pagination element.
	 *
	 * @param  string  $text
	 * @return string
	 */
	public function getNextButton($text = '&raquo;')
	{
		// If the current page is greater than or equal to the last page, it means we
		// can't go any further into the pages, as we're already on this last page
		// that is available, so we will make it the "next" link style disabled.
		if ( ! $this->paginator->hasMorePages())
		{
			return null;
		}

		$url = $this->paginator->url($this->paginator->currentPage() + 1);

		return $this->getPageLinkWrapper($url, $text, 'next');
	}


}



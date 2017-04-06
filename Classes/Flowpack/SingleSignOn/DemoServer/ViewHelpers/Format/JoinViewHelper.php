<?php
namespace Flowpack\SingleSignOn\DemoServer\ViewHelpers\Format;

/*                                                                                       *
 * This script belongs to the Flow Framework package "Flowpack.SingleSignOn.DemoServer". *
 *                                                                                       */

use TYPO3\Flow\Annotations as Flow;

/**
 * Join view helper
 */
class JoinViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Get the distance from the given time to now in words
     *
     * @param array $items
     * @param string $delimiter
     * @return string
     */
    public function render($items, $delimiter = ', ') {
        return join($delimiter, $items);
    }

}


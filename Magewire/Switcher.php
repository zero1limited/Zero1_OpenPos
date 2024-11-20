<?php
declare(strict_types=1);

namespace Zero1\OpenPos\Magewire;

use Magewirephp\Magewire\Component;

class Switcher extends Component
{
    public $listeners = ['toggle'];

    public $loader = 'Please wait...';

    /**
     * @var array
     */
    protected $blocks;

    /**
     * @param array|null $blocks
     */
    public function __construct(
        $blocks = null
    ) {
        $this->blocks = $blocks;
    }

    /**
     * @return array|null
     */
    public function getBlocks(): ?array
    {
        return $this->blocks;
    }

    /**
     * Toggle selected data entry mode
     * 
     * @param string $selectedBlock
     * @return void
     */
    public function toggle(string $selectedBlock): void
    {
        foreach($this->blocks as $mode => $block) {
            if($block == $selectedBlock) {
                $this->emitTo($block, '$set', 'isVisible', true);
            } else {
                $this->emitTo($block, '$set', 'isVisible', false);
            }
        }
    }
}

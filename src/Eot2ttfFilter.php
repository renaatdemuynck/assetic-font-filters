<?php
namespace RDM\Assetic\Filter;

use Assetic\Filter\BaseProcessFilter;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;

/**
 * Runs assets through TTF2EOT.
 *
 * @author Renaat De Muynck <renaat.demuynck@gmail.com>
 */
class Ttf2eotFilter extends BaseProcessFilter
{

    private $ttf2eotBin;

    /**
     * Constructor.
     *
     * @param string $ttf2eotBin Path to the TTF2EOT binary
     */
    public function __construct($ttf2eotBin = '/usr/bin/ttf2eot')
    {
        $this->ttf2eotBin = $ttf2eotBin;
    }

    public function filterLoad(AssetInterface $asset)
    {}

    public function filterDump(AssetInterface $asset)
    {
        $pb = $this->createProcessBuilder(array($this->ttf2eotBin));
        
        $input = tempnam(sys_get_temp_dir(), 'assetic_ttf2eot');
        
        file_put_contents($input, $asset->getContent());
        
        $pb->add($input);
        
        $proc = $pb->getProcess();
        $code = $proc->run();
        
        unlink($input);
        
        if ($code !== 0) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }
        
        $asset->setContent($proc->getOutput());
    }
}

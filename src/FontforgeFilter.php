<?php
namespace RDM\Assetic\Filter;

use Assetic\Filter\BaseProcessFilter;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;

/**
 * Runs assets through Fontforge.
 *
 * @author Renaat De Muynck <renaat.demuynck@gmail.com>
 */
class FontforgeFilter extends BaseProcessFilter
{
    const FORMAT_TTF = 'ttf';
    const FORMAT_OTF = 'otf';
    const FORMAT_WOFF = 'woff';
    const FORMAT_SVG = 'svg';

    private $fontforgeBin;

    private $format;

    /**
     * Constructor.
     *
     * @param string $fontforgeBin Path to the Fontforge binary
     */
    public function __construct($fontforgeBin = '/usr/bin/fontforge')
    {
        $this->fontforgeBin = $fontforgeBin;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function filterLoad(AssetInterface $asset)
    {}

    public function filterDump(AssetInterface $asset)
    {
        $pb = $this->createProcessBuilder(array($this->fontforgeBin));
        
        $input = tempnam(sys_get_temp_dir(), 'assetic_fontforge');
        $output = sys_get_temp_dir() . '/' . basename($input, '.tmp') . '.' . $this->format;
        
        file_put_contents($input, $asset->getContent());
        
        $pb->add('-lang=ff');
        $pb->add('-c');
        $pb->add('Open($1); Generate($2);');
        $pb->add($input);
        $pb->add($output);
        
        $proc = $pb->getProcess();
        $code = $proc->run();
        
        unlink($input);
        
        if ($code !== 0) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }
        
        $asset->setContent(file_get_contents($output));
        
        unlink($output);
    }
}

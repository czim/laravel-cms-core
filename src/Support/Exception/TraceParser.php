<?php
namespace Czim\CmsCore\Support\Exception;

class TraceParser
{

    /**
     * @var string
     */
    protected $charset;

    /**
     * @var string
     */
    protected $fileLinkFormat;

    /**
     * @param null|string $charset
     * @param null|string $fileLinkFormat
     */
    public function __construct($charset = null, $fileLinkFormat = null)
    {
        $this->charset = $charset ?: ini_get('default_charset') ?: 'UTF-8';
        $this->fileLinkFormat = $fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
    }

    /**
     * @param array $traces
     * @return array
     */
    public function parse(array $traces)
    {
        $data = [];

        foreach ($traces as $trace) {

            $rowData = [
                'function' => null,
                'file'     => null,
            ];

            if ($trace['function']) {
                $rowData['function'] = sprintf(
                    'at %s%s%s(%s)',
                    $this->formatClass($trace['class']),
                    $trace['type'],
                    $trace['function'],
                    $this->formatArgs($trace['args'])
                );
            }

            if (isset($trace['file']) && isset($trace['line'])) {
                $rowData['file'] = $this->formatPath($trace['file'], $trace['line']);
            }

            $data[] = $rowData;
        }

        return $data;
    }


    /**
     * @param string $class
     * @return string
     */
    protected function formatClass($class)
    {
        $parts = explode('\\', $class);

        return sprintf('<abbr title="%s">%s</abbr>', $class, array_pop($parts));
    }

    /**
     * @param string $path
     * @param string $line
     * @return string
     */
    protected function formatPath($path, $line)
    {
        $path = $this->escapeHtml($path);
        $file = preg_match('#[^/\\\\]*$#', $path, $file) ? $file[0] : $path;

        if ($linkFormat = $this->fileLinkFormat) {
            $link = strtr($this->escapeHtml($linkFormat), array('%f' => $path, '%l' => (int) $line));

            return sprintf(' in <a href="%s" title="Go to source">%s line %d</a>', $link, $file, $line);
        }

        return sprintf(' in <a title="%s line %3$d" ondblclick="var f=this.innerHTML;this.innerHTML=this.title;this.title=f;">%s line %d</a>', $path, $file, $line);
    }

    /**
     * Formats an array as a string.
     *
     * @param array $args The argument array
     * @return string
     */
    protected function formatArgs(array $args)
    {
        $result = array();
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $formattedValue = sprintf('<em>object</em>(%s)', $this->formatClass($item[1]));
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf('<em>array</em>(%s)', is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('string' === $item[0]) {
                $formattedValue = sprintf("'%s'", $this->escapeHtml($item[1]));
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>'.strtolower(var_export($item[1], true)).'</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', var_export($this->escapeHtml((string) $item[1]), true));
            }

            $result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $key, $formattedValue);
        }

        return implode(', ', $result);
    }

    /**
     * HTML-encodes a string.
     *
     * @param string $str
     * @return string
     */
    protected function escapeHtml($str)
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, $this->charset);
    }

}

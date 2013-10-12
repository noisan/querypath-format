<?php
namespace Noi\QueryPath;

use QueryPath\Extension;
use QueryPath\Query;
use QueryPath\Exception;

/**
 *
 * @author Akihiro Yamanoi <akihiro.yamanoi@gmail.com>
 */
class FormatExtension implements Extension
{
    protected $qp;

    public function __construct(Query $qp)
    {
        $this->qp = $qp;
    }

    public function format($callback, $args = null, $additional = null)
    {
        list($callback, $pos) = $this->prepareCallback($callback);
        if (!is_callable($callback)) {
            throw new Exception('Callback is not callable.');
        }

        if (isset($additional)) {
            $args = func_get_args();
            array_shift($args);
        }

        $padded = $this->prepareArgs($args, $pos);
        foreach ($this->qp as $qp) {
            $padded[$pos] = $qp->text();
            $qp->text(call_user_func_array($callback, $padded));
        }
        return $this->qp;
    }

    protected function prepareCallback($callback)
    {
        if (is_string($callback)) {
            list($callback, $trail) = $this->splitFunctionName($callback);
            $pos = intval($trail);
        } elseif (is_array($callback) and isset($callback[2])) {
            $pos = $callback[2];
            $callback = array($callback[0], $callback[1]);
        } else {
            $pos = 0;
        }
        return array($callback, $pos);
    }

    protected function splitFunctionName($string)
    {
        // 'func_name:2', 'func_name@3', 'func_name[1]', ...
        return preg_split('/[^a-zA-Z0-9_\x7f-\xff][^\d]*|$/', $string, 2);
    }

    protected function prepareArgs($args, $pos)
    {
        $padded = array_pad((array) $args, (0 < $pos) ? $pos - 1 : 0, null);
        array_splice($padded, $pos, 0, array(null)); // insert null as a place holder
        return $padded;
    }
}

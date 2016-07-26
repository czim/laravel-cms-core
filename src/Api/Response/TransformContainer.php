<?php
namespace Czim\CmsCore\Api\Response;

use Czim\CmsCore\Contracts\Api\Response\TransformContainerInterface;
use Czim\DataObject\AbstractDataObject;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Class TransformContainer
 *
 * Container for serializable, transformable content, to be handled by a response builder.
 *
 * @property mixed                      $content
 * @property string|TransformerAbstract $transformer
 * @property bool                       $collection
 */
class TransformContainer extends AbstractDataObject implements TransformContainerInterface
{
    protected $attributes = [
        'content'     => null,
        'transformer' => null,
        'collection'  => null,
    ];

    /**
     * Returns transformable content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->getAttribute('content');
    }

    /**
     * The transformer instance, or an FQN for the transformer to instantiate.
     *
     * @return string|TransformerAbstract
     */
    public function getTransformer()
    {
        return $this->getAttribute('transformer');
    }

    /**
     * Returns whether the content is a collection of transformables.
     *
     * @return bool
     */
    public function isCollection()
    {
        // If collection is not explicitly set, assume standard Laravel use,
        // where either collections or models are passed in as content.

        if (null === $this->getAttribute('collection')) {
            return ($this->getContent() instanceof Collection);
        }

        return $this->getAttribute('collection');
    }
}

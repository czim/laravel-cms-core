<?php
namespace Czim\CmsCore\Contracts\Api\Response;

use Czim\DataObject\Contracts\DataObjectInterface;
use League\Fractal\TransformerAbstract;

interface TransformContainerInterface extends DataObjectInterface
{

    /**
     * Returns transformable content
     *
     * @return mixed
     */
    public function getContent();

    /**
     * The transformer instance, or an FQN for the transformer to instantiate.
     *
     * @return string|TransformerAbstract
     */
    public function getTransformer();

    /**
     * Returns whether the content is a collection of transformables.
     *
     * @return bool
     */
    public function isCollection();

}

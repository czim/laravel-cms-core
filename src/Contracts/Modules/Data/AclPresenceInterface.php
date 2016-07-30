<?php
namespace Czim\CmsCore\Contracts\Modules\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface AclPresenceInterface extends DataObjectInterface
{

    /**
     * Returns unique identifier for the ACL presence.
     *
     * @return string
     */
    public function id();

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\AclPresenceType
     * @return string
     */
    public function type();

    /**
     * Returns available permission(s).
     *
     * @return null|string|string[]
     */
    public function permissions();

    /**
     * Returns (translatable) label for display.
     *
     * @return string
     */
    public function label();

    /**
     * Returns wether the label() is a translation key.
     *
     * @return bool
     */
    public function translated();

}

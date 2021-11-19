<?php

namespace Types;

/**
 * Container for database search options
 * @package Types
 */
class MaterialSearchOptions
{
    /**
     * Specify a title of an material to search in database
     * @var string|null
     */
    public ?string $title = null;

    /**
     * Specify a short material content in db
     * @var string|null
     */
    public ?string $short = null;

    /**
     * Specify the time to search for materials whose publication
     * is greater than or equal to this
     *
     * If time_end is not specified, only search for greater
     * or equal (> =) is performed
     * @var int|null
     */
    public ?int $time_start = null;

    /**
     * Specify the time to search for materials whose publication
     * is lower than or equal to this
     * @var int|null
     */
    public ?int $time_end = null;

    /**
     * Specify the tag that materials should include
     * @var string|null
     */
    public ?string $tag = null;

    /**
     * Specify material identifier
     *
     * all other variables will not take effect if this is enabled
     * @var string|null
     */
    public ?string $identifier = null;

    /**
     * Indicate the pinned state in which the materials should be
     * @var bool|null
     */
    public ?bool $pinned = null;

    /**
     * Offset for the materials search
     * @var int|null
     */
    public ?int $offset = null;

}

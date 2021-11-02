<?php


namespace Types;

/**
 * List of action types (Action:(...))
 * @package Types
 */
class RequestActionsList
{
    // Read-only requests (no auth required)
    /** @var string request list of all tags from database */
    public const getTagsList = "0";

    /** @var string request one latest pinned material from database */
    public const getPinnedMaterial = "1";

    /** @var string request several latest materials from database */
    public const getLatestMaterials = "2";

    // Read-write requests (auth required)
    /** @var string update material at the server from client data */
    public const updateMaterial = "3";

    /** @var string remove db entries and all files of the specific material */
    public const removeMaterial = "4";

    // Accounts read-write requests (auth required)
    /** @var string change account password */
    public const changePassword = "5";
}
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
    public const getMaterials = "2";

    // Read-write requests (auth required)
    /** @var string update material at the server from client data */
    public const updateMaterial = "3";

    /** @var string remove db entries and all files of the specific material */
    public const removeMaterial = "4";

    // Accounts read-write requests (auth required)
    /** @var string change account password */
    public const changePassword = "5";

    // Files upload requests (auth required)
    /** @var string upload file (any) */
    public const uploadFile = "6";

    /** @var string get list of files from local storage file system */
    public const getFilesList = "7";

    /** @var string get list images (same as getFilesList) */
    public const getImagesList = "8";

    /** @var string get material json content from file */
    public const getFullMaterial = "9";

    /** @var string verify account data */
    public const verifyAccount = "10";

    /** @var string verify google recaptcha token */
    public const verifyCaptchaRequest = "11";

    /** @var string get content from properties table */
    public const getFromProperties = "12";

    /** @var string update property value in database */
    public const updateProperty = "13";

    /** @var string remove file from user storage */
    public const removeFile = "14";
}

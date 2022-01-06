<?php

namespace Types;

class PostRequests {
    public const recaptchaToken = "recaptchaToken";
    public const accountLogin = "accountLogin";
    public const accountHash = "accountHash";
    public const uploadFile = "uploadFile";
}

class MaterialSearchPostRequests {
    public const title = ["title like", "%find:materialTitle%"];
    public const description = [ "description like", "%find:materialDescription%" ];

    public const content = "find:materialContent";
    public const tags = "find:materialTags";

    public const datetimeFrom = [ "datetime >=", "find:materialDatetimeFrom" ];
    public const datetimeTo = ["datetime <=", "find:materialDatetimeTo"];
    public const identifier = ["identifier =", "find:materialIdentifier"];
}

class FilesSearchPostRequests {
    public const fileName = ["filename like", "%find:fileName%"];
}

class CommonSearchPostRequests {
    public const limit = "find:limit";
}
<?php

namespace Types;

class RequestTypesList
{
    public const Action = "Request:Action";
    public const AccountLogin = "Account:Login";
    public const AccountHash = "Account:Hash";
    public const AccountNewHash = "Account:NewHash";

    public const DataTag = "Data:Tag";
    public const DataLimit = "Data:Limit";
    public const DataFindPinned = "Data:FindPinned";
    public const DataTitle = "Data:Title";
    public const DataTimeStart = "Data:TimeStart";
    public const DataTimeEnd = "Data:TimeEnd";
    public const DataIdentifier = "Data:Identifier";
    public const DataOffset = "Data:Offset";
    public const DataShort = "Data:Short";

    public const CaptchaToken = "Captcha:ResponseToken";

    public const Property = "Database:Property";
    public const PropertyValue = "Database:PropertyValue";

    public const FileName = "File:Name";
    public const FileDate = "File:Date";

    public const UpdateIdentifier = "Update:Identifier";
    public const UpdateContent = "Update:Content";
    public const UpdatePinned = "Update:Pinned";
    public const UpdateTitle = "Update:Title";
    public const UpdateTime = "Update:Time";
    public const UpdateTags = "Update:Tags";
    public const UpdateShort = "Update:Short";
}

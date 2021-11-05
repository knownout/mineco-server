# Ministry of Agriculture and Natural Resources web application server API

The server API contains two types of requests: 
1. Only reading data from the database and server files
2. Reading and writing data

Read-only requests only need their own parameters to call 
(Number of items, identifier, title, and so on)

Requests that modify (write) the database and server files require, 
in addition to their own parameters, additional user authorization parameters.

Data is transmitted using POST requests 
(excluding file upload and image view handlers, they use GET)

## Read-only request (without authentication)
```typescript
// Request types numeration
enum RequestTypes {
    getTagsList = 0,
    getPinnedMaterial = 1,
    getMaterials = 2,
    updateMaterial = 3,
    removeMaterial = 4,
    changePassword = 5
}

// Options of the request
const options = {
    "Request:Action": RequestTypes.getTagsList
};

const formData = new FormData();

// Append values form options to the form data
Object.keys(options)
    .forEach(i => formData.append(i, options[i]));
```


Result of executing the code above is a list of all available 
(displayed) tags from the database

Available request types are listed in the 
`RequestTypes` object in the code above

Read-only queries can take the following parameters (legacy, might not work):

| Option | Type | Description |
| ------ | ---- | ----------- |
| Request:Action | int | type of the request |
| DataTag        | string | search materials by tag |
| DataLimit      | int | set limit of search result |
| DataFindPinned | boolean | find pinned materials too |
| DataTitle      | string | find materials by title |
| DataTimeStart  | int | find materials after date |
| DataTimeEnd    | int | find materials before date |
| DataIdentifier | string | find material by identifier |

If DataIdentifier specified, other options will no take effect
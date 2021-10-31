# SERVER STRUCTURE
Description of the basic structure of the
server in the form of classes (files), requests
to them and responses in free-form.

## Material worker
A class, or a set of classes, for working
with materials (articles, pages and site documents)

### Read-only methods
Read-only methods do not require pass of authorization 
data for their execution and cannot work with the
functions of writing or changing server files

```
PREVIEW <= identifiers[]
	array
		?preview image src with size def
		?article first paragraph

		material title
		material identifier
		material date
		material date shown

MATERIALS <= identifiers[]
! GET FROM MYSQL DB
	array
		material title (same as page title)
		material datafile content
		material date
		material date shown

LIST <= tag, offset, count
	array
		identifier

LENGTH <= tag
	number
```

### Update
Update methods require authorization data
and can modify server files

```

UPDATE <= title, datafile content, images. files, identifier
	=> result

! UPDATE method can create new materials with specified identifier

REMOVE <= identifier
	=> result
```

## Accounts worker
A class, or a set of classes, for working with user accounts
(receiving and processing data from a database, mapping)

```
AUTHORIZE <= password hash, login
	=> result

CREATE <= password, login
	=> result

REMOVE <= password hash, login
```

## WORKERS WITHOUT REQUESTS

One or more classes that do not accept or process
requests and are needed to work with the server's
internal infrastructure

### Output worker
Class for decorating content displayed to the user

```
SET JSON HEADER
SET IMAGE HEADER
SET DOWNLOAD HEADER
SET PREVIEW HEADER
SET CORS HEADERS

MAKE JSON OUTPUT
MAKE NOTFOUND OUTPUT

RETURN OUTPUT
```

### Files worker
Class for working with server files

```
READ JSON FILE
WRITE JSON FILE

WRITE UPLOADED IMAGES <= images, identifier
WRITE UPLOADED FILES <= files, identifier

REMOVE FOLDER
```

#BASIC CONCEPTS OF THE SERVER

1. Files of the material stored in the material folder only (no outer deps)
2. Files and images upload processes only after material published (no pre-loading)
3. Info about materials (title, tags, date, path) stored in MySQL database
4. ALWAYS reduce images quality to 90 when uploading
5. Always reduce image size if more than 1920 on width or height
6. Reduce image quality if size more than 500kb

#MATERIALS STRUCTURE
```
FILE SYSTEM:
    materials/
        ! ALL IMAGES QUALITY IS 90
        ! MAX IMAGE WIDTH 1920
        ! MAX IMAGE HEIGHT 1920
        <identifier: string(10)>/
            preview/
            ! MIN PREVIEW SIZE 320x400 PIXELS

            ! PREVIEW ASPECT RATIO ALWAYS 16x9

                preview-tile.jpg
                preview.jpg
                preview-large.jpg
                preview-og.jpg

            images/<any jpg>
            files/<any>
            content.json
                title: string
                time: number
                blocks: EditorData

DATABASE STRUCTURE:
    materials table/
        identifier: string(10) key
        title: string
        tags: string[]
        time: number

    tags table/
        id: number key
        name: string
    
```
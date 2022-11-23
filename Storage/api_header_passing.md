# Storage API checks for header passing

We will [test storage apis](https://github.com/googleapis/conformance-tests/blob/main/storage/v1/retry_tests.json) with sending additional header with the request and see if that header is properly passing to the api request or not.

The base `work()` function is as follows:
```php
private function work($storage, $options)
{
    // Names
    $bucketName = sprintf('bucket' . time());
    $objectName = sprintf('object' . time());

    // Bucket creation
    $bucket = $storage->createBucket($bucketName);

    // Object creation and upload
    $uploadPath = tempnam(sys_get_temp_dir(), '/tests');
    $fileName = basename($uploadPath);
    file_put_contents($uploadPath, 'Sample Content: ' . rand());
    $file = fopen($uploadPath, 'r');
    $object = $bucket->upload($file, [
        'name' => $objectName,
    ]);

    // Your code

    // Cleanup
    foreach ($bucket->objects() as $object) {
        $object->delete();
    }
    $bucket->delete();
}
```

Format for each entry is as follows:

## API [Verdict]

* Rest: function
### Usages

----

## storage.bucket_acl.get [Failure]

* Rest: `getAcl()`

### $bucket->acl()->get()
Header not passed
```php
// For bucket acl
$acl = $bucket->acl();
$aclList = $acl->get($options);
```

This passes, but needs this line in `$options`:
```
$options['entity'] = 'allAuthenticatedUsers';
```

> I got to know this by reading the definition of `Acl::get()` method, if this option is present, only then `$this->connection->getAcl()` is called.

---

## storage.bucket_acl.list [success]

* Rest: `listAcl()`
### `$bucket->acl()->get()`
Success

```php
$acl = $bucket->acl();
$aclList = $acl->get($options);
```

---

## storage.buckets.delete [Success]

* Rest: deleteBucket()
### $bucket->delete()

```php
$bucket->delete($options);
```

---

## storage.buckets.get [Partial]

* Rest: getBucket()

Used in two places, `$bucket->exists()` and `$bucket->reload()`

### $bucket->exists()
Headers not passed!
```php
$bucket->exists($options);
```

### $bucket->reload()
Success
```php
$bucket->reload($options);
```

---

## storage.buckets.getIamPolicy [Success]

* Rest: `getBucketIamPolicy()`

### $iam->reload()

```php
$iam = $bucket->iam();
$iam->reload($options);
```

---

## storage.buckets.list [Verdict]

* Rest: `listBuckets()`

### $storage->buckets()
Headers not passed

```php
$options['maxResults'] = 10;
$storage->buckets($options);
```

---

## storage.buckets.insert [Success]

* Rest: `insertBucket()`

### $storage->createBucket()
Success

```php
$options['labels'] = ['testing' => 'php'];
$bucket = $storage->createBucket($bucketName, $options);
```

---

## storage.buckets.lockRetentionPolicy [Success]

* Rest: `lockRetentionPolicy()`

### $bucket->lockRetentionPolicy()
Success

```php
$options['ifMetagenerationMatch'] = '1';
$bucket->lockRetentionPolicy($options);
```

---

## storage.buckets.testIamPermissions [Success]

* Rest: `testBucketIamPermissions()`

### $iam->testPermissions()
Success
```php
$iam = $bucket->iam();
$iam->testPermissions([], $options);
```

---

## storage.default_object_acl.get [Failed]

* Rest: `getAcl()`

###  $bucket->defaultAcl()->get()
Headers not passed

```php
$acl = $bucket->defaultAcl();
$aclList = $acl->get($options);
```

This passes, but needs this line in `$options`:
```
$options['entity'] = 'allAuthenticatedUsers';
```
---

## storage.default_object_acl.list [success]

* Rest: `listAcl()`
### $bucket->defaultAcl()->get()
success

```php
$acl = $bucket->defaultAcl();
$aclList = $acl->get($options);
```
---

## storage.hmacKey.create [success]

* Rest: `createHmacKey()`

### $storage->createHmacKey()

> Doubt: There is no `storage.hmacKey.create` in the Rest.php file. Only `projects.resources.hmacKeys.create` is there. And only `storage.hmacKey.create` works with the emulator, other one throws BAD REQUEST:400 error.

Success
```php
$options['projectId'] = 'test';
$storage->createHmacKey('temp@test.iam.gserviceaccount.com', $options);
```

---

## storage.hmacKey.get [success]

* Rest: `getHmacKey()`

### $hmacKey->reload()
Success
```php
$createdHmacKey = $storage->createHmacKey('temp@test.iam.gserviceaccount.com');
$hmacKey = $createdHmacKey->hmacKey();
$hmacKey->reload($options);
```

---

## storage.hmacKey.list[Failed]

* Rest: `listHmacKeys()`

### $storage->hmacKeys()
Headers not passed
```php
$storage->hmacKeys($options);
```
Only calling `hmacKeys()` never calls the API. The API is called once we loop the keys. It's like lazy loading.
```
$keys = $storage->hmacKeys($options);
foreach($keys as $key){
    echo $key->accessId() . "\n";
}
```
---

## storage.hmacKey.update [success]

* Rest: `updateHmacKey()`

### $hmacKey->update()
Success
```php
$createdHmacKey = $storage->createHmacKey('temp@test.iam.gserviceaccount.com');
$hmacKey = $createdHmacKey->hmacKey();
$hmacKey->update('INACTIVE', $options);
```

---

## storage.hmacKey.delete [success]

* Rest: `deleteHmacKey()`

### $hmacKey->delete()
Success
```php
$createdHmacKey = $storage->createHmacKey('temp@test.iam.gserviceaccount.com');
$hmacKey = $createdHmacKey->hmacKey();
$hmacKey->update('INACTIVE');
$hmacKey->delete($options);
```

---

## storage.notifications.get [Partial]

* Rest: `getNotification()`

### $notification->exists()
Headers not passed
```php
$notification = $bucket->createNotification('Anything');
$notification->exists($options);
```

### $notification->reload()
Success
```php
$notification = $bucket->createNotification('Anything');
$notification->reload($options);
```

---

## storage.notifications.list [Failed]

* Rest: `listNotifications()`

### $bucket->notifications()
Headers not passed
```php
$bucket->notifications($options);
```

Just like before only calling `notifications()` never calls the API. The API is called once we loop the objects.
```
$objs = $bucket->notifications($options);
foreach($objs as $obj){
}
```
---

## storage.notifications.insert [success]

* Rest: `insertNotification()`

### $bucket->createNotifications()
Success
```php
$notification = $bucket->createNotification('Anything', $options);
```
---

## storage.notifications.delete [success]

* Rest: `deleteNotification()`

### $notification->delete()
Success
```php
$notification = $bucket->createNotification('Anything');
$notification->delete($options);
```

---

## storage.objects.get [success]

* Rest:  `getObject()`

### $object->exists()
Success
```php
$object->exists($options);
```

### $object->reload()
Success
```php
$object->reload($options);
```

---

## storage.objects.list [Failed]

* Rest: `listObjects()`

### $bucket->objects()
Headers not passed
```php
$bucket->objects()
```
Just like before, this needs a loop and it passes.

---

## storage.serviceaccount.get [success]

> Doubt: In Rest.php, it's showing projects.resources.serviceAccount.get
* Rest: `getServiceAccount()`

### $storage->getServiceAccount()
Success
```php
$options['userProject'] = 'test';
$serviceAccountEmail = $storage->getServiceAccount($options);
```

---

## storage.buckets.patch [success]

* Rest: `patchBucket()`

### $bucket->update()
Success
```php
$options['labels'] = ['my_key' => 'my_value'];
$bucket->update($options);
```

---

## storage.buckets.setIamPolicy [success]

* Rest: `setBucketIamPolicy()`

### $bucket->iam->setPolicy()
Success
```php
$bucketIam = $bucket->iam();
$oldPolicy = $bucketIam->policy();
$bucketIam->setPolicy($oldPolicy, $options);
```

---

## storage.buckets.update [Issue]
There is not `storage.buckets.update`. Updates happen through  `storag.buckets.patch`

* Rest: function

### Usages

```php

```

---

## storage.objects.compose [success]

* Rest: `composeObject()`

### $bucket->compose()
Success
```php
$uploadPath = tempnam(sys_get_temp_dir(), '/tests');
file_put_contents($uploadPath, 'Sample Content: ' . rand());
$file = fopen($uploadPath, 'r');
$object_1 = $bucket->upload($file, [
    'name' => $objectName . '_1'
]);
$sourceObjects = [
  $object,
  $object_1
];
$options['metadata'] = [
    'contentType' => 'application/octet-stream'
];
$bucket->compose(
  $sourceObjects,
  sprintf('composed_object' . time()),
  $options
);
```

---

## storage.objects.copy [success]

* Rest: `copyObject()`;

### $object->copy()
Success
```php
$options['name'] = $objectName . '-copy';
$object->copy($bucketName, $options);
```

---

## storage.objects.delete [success]

* Rest: `deleteObject()`

### $object->delete()
Success

```php
// Edited the cleanup part
$object->delete($options);
```

---

## storage.objects.insert [Partial]

> Doubt: Assumed that insertObject used `storage.objects.insert`

* Rest: `insertObject()`

### $bucket->upload()
Headers not passed
```php
// Edited the object creation and upload part
$object = $bucket->upload(
    $file,
    [
        'name' => $objectName,
    ],
    $options
);
```

### $bucket->uploadAsync()
Headers not passed
```php
// Edited the object creation and upload part
$promise = $bucket->uploadAsync(
    $file,
    [
        'name' => $objectName,
    ],
    $options
);
$object = $promise->wait();
```

### $bucket->getResumableUploader()
Success
```php
// Edited the object creation and upload part
$uploader = $bucket->getResumableUploader($file, $options);
try {
    $object = $uploader->upload();
} catch (GoogleException $ex) {
    $resumeUri = $uploader->getResumeUri();
    $object = $uploader->resume($resumeUri);
}
```

### $bucket->getStreamableUploader()
Headers not passed
```php
$uploader = $bucket->getStreamableUploader(
    $file,
    [
        'name' => $objectName
    ],
    $options
);
$object = $uploader->upload();
```
---

## storage.objects.patch [success]

* Rest: `patchObject()`

### $object->update()
Success
```php
$options['projection'] = 'full';
$object->update(
    [ 'name' => $objectName . '-updated'],
    $options
);
```

---

## storage.objects.rewrite [success]

* Rest: `rewriteObject()`

### Usages
Success
```php
$options['name'] = $objectName . '-rewritten';
$rewrittenObject = $object->rewrite($bucket, $options);
```

---

## storage.objects.update [Issue]
There is not `storage.buckets.update`. Updates happen through  `storag.buckets.patch`

* Rest: function

### Usages

```php

```

---

## storage.bucket_acl.delete [success]

* Rest: function

### $bucket->acl()->delete()
Success
```php
$acl = $bucket->acl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$acl->delete($entity, $options);
```

---

## storage.bucket_acl.insert [success]

* Rest: `insertAcl()`

### $bucket->acl()->insert()
Success
```php
$acl = $bucket->acl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$acl->add($entity, 'WRITER', $options);
```

---

## storage.bucket_acl.patch [success]

* Rest:  `patchAcl()`

### $bucket->acl()->update()
Success
```php
$acl = $bucket->acl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$acl->update($entity, 'READER', $options);
```

---

## storage.bucket_acl.update [Issue]

API not found in the rest file
* Rest: function

### Usages

```php

```

---

## storage.object_acl.get [Failed]

* Rest: `getAcl()`

### $object->acl()->get()
Headers not passed
```php
$acl = $object->acl();
$aclList = $acl->get($options);
```
Just like before this passes, but needs this line in `$options`:
```
$options['entity'] = 'allAuthenticatedUsers';
```
---

## storage.object_acl.list [success]

* Rest: `listAcl()`

### $object->acl()->list()
Success
```php
$acl = $object->acl();
$aclList = $acl->get($options);
```

---

## storage.object_acl.delete [success]

* Rest: `deleteAcl()`

### Usages

```php
$acl = $object->acl();
$aclList = $acl->get();
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->delete($entity, $options);
```

---

## storage.object_acl.insert [success]

* Rest: `insertAcl()`

### $object->acl()->add()
Success
```php
$acl = $object->acl();
$aclList = $acl->get();
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->add($entity, $role, $options);
```

---

## storage.object_acl.patch [success]

* Rest: `patchAcl()`

### $object->acl()->update()
Success
```php
$acl = $object->acl();
$aclList = $acl->get();
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->update($entity, $role, $options);
```

---

## storage.object_acl.update [Issue]
Rest file doesn't has this API method.
* Rest: function

### Usages

```php

```

---

## storage.default_object_acl.insert [success]

* Rest: `insertAcl()`

### $bucket->defaultAcl()->add()
Success
```php
$acl = $bucket->defaultAcl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->add($entity, $role, $options);
```

---

## storage.default_object_acl.patch [success]

* Rest:  `patchAcl()`

### Usages
Success
```php
$acl = $bucket->defaultAcl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->update($entity, $role, $options);
```

---

## storage.default_object_acl.delete [success]

* Rest: `deleteAcl()`

### Usages
Success
```php
$acl = $bucket->defaultAcl();
$aclList = $acl->get($options);
$entity = $aclList[0]['entity'];
$role = $aclList[0]['role'];
$acl->delete($entity, $options);
```

---

## storage.default_object_acl.update [Issue]
Rest file doesn't has this API method.
* Rest: `function`

### Usages

```php

```

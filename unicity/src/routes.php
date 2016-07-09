<?php
// Routes

$authCheck = function ($request, $response, $next) {
    if (!isset($_SESSION['user'])) {
        return $response->withStatus(302)->withHeader('Location', '/');
    }
    return $next($request, $response);
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'login.twig.html', []);
});

$app->post('/login', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT * FROM user WHERE email = :username AND pass = PASSWORD(:password)");
    $parsedBody = $request->getParsedBody();
    $stmt->execute([':username' => $parsedBody['email'], ':password' => $parsedBody['pass']]);
    $result = $stmt->fetch();
    if (empty($result)) {
        return $this->view->render($response, 'login.twig.html', ['error' => 'incorrect password']);
    } else {
        $_SESSION['user'] = $result;
        if ($result['change_pass'] == 1) {
            return $response->withStatus(302)->withHeader('Location', '/changepass');
        }
        return $response->withStatus(302)->withHeader('Location', '/dashboard');
    }
});

$app->get('/dashboard', function ($request, $response, $args) {
    $args = ['activeDashboard' => 'active'];
    $userid = $_SESSION['user']['id'];
    $pdo = $this->pdo;
    return $this->view->render($response, 'dashboard.twig.html', $args);
})->add($authCheck);



$app->get('/country', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT * FROM country");
    $stmt->execute();
    $items = $stmt->fetchAll();
    $args = ['activeCountry' => 'active'];
    $args = array_merge(['items' => $items], $args);
    return $this->view->render($response, 'country.twig.html', $args);
})->add($authCheck);


$app->post('/country', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $stmt = $this->pdo->prepare("INSERT INTO country (name) VALUES (:name)");
    $stmt->execute([':name' => $parsedBody['name']]);
    return $response->withStatus(302)->withHeader('Location', '/country');
})->add($authCheck);


$app->get('/document-category', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT * FROM document_category");
    $stmt->execute();
    $items = $stmt->fetchAll();
    $args = ['activeDocCat' => 'active'];
    $args = array_merge(['items' => $items], $args);
    return $this->view->render($response, 'doccat.twig.html', $args);
})->add($authCheck);


$app->post('/document-category', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $stmt = $this->pdo->prepare("INSERT INTO document_category (name) VALUES (:name)");
    $stmt->execute([':name' => $parsedBody['name']]);
    return $response->withStatus(302)->withHeader('Location', '/document-category');
})->add($authCheck);

$app->get('/media-category', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT * FROM media_category");
    $stmt->execute();
    $items = $stmt->fetchAll();
    $args = ['activeMediaCat' => 'active'];
    $args = array_merge(['items' => $items], $args);
    return $this->view->render($response, 'mediacat.twig.html', $args);
})->add($authCheck);


$app->post('/media-category', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $stmt = $this->pdo->prepare("INSERT INTO media_category (name) VALUES (:name)");
    $stmt->execute([':name' => $parsedBody['name']]);
    return $response->withStatus(302)->withHeader('Location', '/media-category');
})->add($authCheck);

$app->get('/photos', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT id, filename, title, checksum FROM photo");
    $stmt->execute();
    $items = $stmt->fetchAll();
    $args = ['activePhotos' => 'active'];
    $args = array_merge(['items' => $items], $args);
    return $this->view->render($response, 'photo.twig.html', $args);
})->add($authCheck);

$app->get('/delete_photo/{id}', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $stmt = $this->pdo->prepare("DELETE FROM photo WHERE id = :id ");
    $stmt->execute([':id' => $id]);
    return $response->withStatus(302)->withHeader('Location', '/photos');
})->add($authCheck);


$app->get('/delete_document/{id}', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $stmt = $this->pdo->prepare("DELETE FROM document WHERE id = :id ");
    $stmt->execute([':id' => $id]);
    return $response->withStatus(302)->withHeader('Location', '/documents');
})->add($authCheck);


$app->get('/delete_media/{id}', function ($request, $response, $args) {
    $id = $request->getAttribute('id');
    $stmt = $this->pdo->prepare("DELETE FROM media WHERE id = :id ");
    $stmt->execute([':id' => $id]);
    return $response->withStatus(302)->withHeader('Location', '/media');
})->add($authCheck);


$app->post('/photo', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    if (empty($files['photo'])) {
        return $response->withStatus(302)->withHeader('Location', '/photo');
    }
    $filename = $files['photo']->getClientFilename();
    $stream = $files['photo']->getStream();
    $content = $stream->read($stream->getSize());
    $checksum = md5($content);
    $title = $parsedBody['title'];

    $stmt = $this->pdo->prepare("INSERT INTO photo (filename, title, content, checksum) VALUES (:filename, :title, :content, :checksum)");
    $stmt->execute([':filename' => $filename, ':title' => $title, ':content' => $content, ':checksum' => $checksum]);
    return $response->withStatus(302)->withHeader('Location', '/photos');
})->add($authCheck);

//document
$app->get('/documents', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT d.id, c.name AS country, dc.name AS category, d.filename, d.title, d.checksum "
       . "FROM document AS d "
       . "JOIN country AS c ON c.id = d.country_id "
       . "JOIN document_category AS dc ON dc.id = d.document_category_id ");
    $stmt->execute();
    $items = $stmt->fetchAll();

    $sql = "SELECT * FROM country";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $countries = $stmt->fetchAll();

    $sql = "SELECT * FROM document_category";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll();

    $args = ['activeDocuments' => 'active', 'items' => $items, 'countries' => $countries, 'categories' => $categories];
    return $this->view->render($response, 'document.twig.html', $args);
})->add($authCheck);

$app->post("/getImagesSince", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, title, checksum FROM photo WHERE UNIX_TIMESTAMP(created) > :created";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':created' => $parsedBody['lastUpdate']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});

$app->post("/getDocumentsSince", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, title, checksum FROM document WHERE UNIX_TIMESTAMP(created) > :created";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':created' => $parsedBody['lastUpdate']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});

$app->post("/getMediasSince", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, title, checksum FROM media WHERE UNIX_TIMESTAMP(created) > :created";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':created' => $parsedBody['lastUpdate']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});


$app->post("/getDocumentCategories", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, name FROM document_category WHERE UNIX_TIMESTAMP(created) > :created";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':created' => $parsedBody['lastUpdate']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});

$app->post("/getMediaCategories", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, name FROM media_category WHERE UNIX_TIMESTAMP(created) > :created";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':created' => $parsedBody['lastUpdate']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});


$app->post("/getImage", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT id, filename, title, checksum, TO_BASE64(content) AS content FROM photo WHERE id = :id ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $parsedBody['id']]);
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});

$app->post("/getDocument", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT document.id, filename, title, checksum, TO_BASE64(content) AS content, "
           ." document_category.name AS catname, country.name AS country_name  FROM document "
           ." JOIN document_category ON document_category.id = document.document_category_id "
           ." JOIN country ON country.id = document.country_id "
           ." WHERE document.id = :id ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $parsedBody['id']]);
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});

$app->post("/getMedia", function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $sql = "SELECT media.id, filename, title, checksum, TO_BASE64(content) AS content, "
        ." media_category.name AS catname FROM media "
        ." JOIN media_category ON media_category.id = media.media_category_id "
        ." WHERE media.id = :id ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $parsedBody['id']]);
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    $result = ['status' => 0, 'rows' => count($rows), 'result' => $rows ];
    return $response->withJson($result);
});


$app->post('/documents', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    if (empty($files['document'])) {
        return $response->withStatus(302)->withHeader('Location', '/documents');
    }
    $filename = $files['document']->getClientFilename();
    $stream = $files['document']->getStream();
    $content = $stream->read($stream->getSize());
    $checksum = md5($content);
    $title = $parsedBody['title'];

    $stmt = $this->pdo->prepare("INSERT INTO document (filename, title, content, checksum, country_id, document_category_id) VALUES (:filename, :title, :content, :checksum, :country, :doccat)");
    $stmt->execute([
        ':filename' => $filename, ':title' => $title,
        ':content' => $content, ':checksum' => $checksum,
        ':country' => $parsedBody['country_id'], ':doccat' => $parsedBody['document_category_id']
    ]);
    return $response->withStatus(302)->withHeader('Location', '/documents');
})->add($authCheck);

//media
$app->get('/media', function ($request, $response, $args) {
    $stmt = $this->pdo->prepare("SELECT d.id, dc.name AS category, d.filename, d.title, d.checksum "
        . "FROM media AS d "
        . "JOIN media_category AS dc ON dc.id = d.media_category_id ");
    $stmt->execute();
    $items = $stmt->fetchAll();

    $sql = "SELECT * FROM country";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $countries = $stmt->fetchAll();

    $sql = "SELECT * FROM media_category";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll();


    $args = ['activeMedia' => 'active'];
    $args = array_merge(['items' => $items, 'countries' => $countries, 'categories' => $categories], $args);
    return $this->view->render($response, 'media.twig.html', $args);
})->add($authCheck);


$app->post('/media', function ($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    if (empty($files['media'])) {
        return $response->withStatus(302)->withHeader('Location', '/media');
    }
    $filename = $files['media']->getClientFilename();
    $stream = $files['media']->getStream();
    $content = $stream->read($stream->getSize());
    $checksum = md5($content);
    $title = $parsedBody['title'];

    $stmt = $this->pdo->prepare("INSERT INTO media (filename, title, content, checksum, media_category_id) VALUES (:filename, :title, :content, :checksum, :doccat)");
    $stmt->execute([
        ':filename' => $filename, ':title' => $title,
        ':content' => $content, ':checksum' => $checksum,
        ':doccat' => $parsedBody['media_category_id']
    ]);
    return $response->withStatus(302)->withHeader('Location', '/media');
})->add($authCheck);


$app->get('/logout', function ($request, $response, $args) {
    session_destroy();
    return $response->withStatus(302)->withHeader('Location', '/');
})->setName('logout')->add($authCheck);


$app->get('/changepass', function ($request, $response, $args) {
    return $this->view->render($response, 'changepass.twig.html', $args);
})->setName('changepass')->add($authCheck);

$app->post('/changepass', function ($request, $response, $args) {
    $pdo = $this->pdo;
    $parsedBody = $request->getParsedBody();
    $userid = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("UPDATE user SET pass = PASSWORD(:pass), change_pass = 0 WHERE id = :id");
    $stmt->execute([":pass" => $parsedBody['pass'], ":id" => $userid]);

    return $response->withStatus(302)->withHeader('Location', '/dashboard');

})->setName('changepasspost')->add($authCheck);

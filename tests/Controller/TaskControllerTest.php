<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    public function testGetTasks()
    {
        $client = static::createClient();
        // Получаем токен
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nisagaliev035@gmail.com',
            'password' => '123'
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;
        $this->assertNotNull($token, 'JWT token not received');
        $client->request('GET', '/api/tasks', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testCreateTaskValidation()
    {
        $client = static::createClient();
        // Получаем токен
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nisagaliev035@gmail.com',
            'password' => '123'
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;
        $this->assertNotNull($token, 'JWT token not received');
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token,
        ], json_encode([
            'name' => '', // пустое имя, должно быть ошибка
            'description' => 'desc',
            'status' => 'новая'
        ]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetTasksWithAuth()
    {
        $client = static::createClient();

        // 1. Получаем токен
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nisagaliev035@gmail.com',
            'password' => '123'
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;
        $this->assertNotNull($token, 'JWT token not received');

        // 2. Делаем запрос с токеном
        $client->request('GET', '/api/tasks', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
    }

    public function testCreateTaskValidationWithAuth()
    {
        $client = static::createClient();

        // Получаем токен
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nisagaliev035@gmail.com',
            'password' => '123'
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;
        $this->assertNotNull($token, 'JWT token not received');

        // Делаем запрос с токеном
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token,
        ], json_encode([
            'name' => '',
            'description' => 'desc',
            'status' => 'новая'
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateAndCrudTaskWithAuth()
    {
        $client = static::createClient();

        // Получаем токен
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'nisagaliev035@gmail.com',
            'password' => '123'
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'] ?? null;
        $this->assertNotNull($token, 'JWT token not received');
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token,
        ];

        // 1. Создание задачи
        $client->request('POST', '/api/tasks', [], [], $headers, json_encode([
            'name' => 'Тестовая задача',
            'description' => 'Описание',
            'status' => 'новая'
        ]));
        $this->assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $created);
        $taskId = $created['id'];

        // 2. Получение задачи по id
        $client->request('GET', '/api/tasks/' . $taskId, [], [], $headers);
        $this->assertResponseIsSuccessful();
        $task = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Тестовая задача', $task['name']);

        // 3. Обновление задачи
        $client->request('PUT', '/api/tasks/' . $taskId, [], [], $headers, json_encode([
            'name' => 'Обновлено',
            'description' => 'Новое описание',
            'status' => 'в процессе'
        ]));
        $this->assertResponseIsSuccessful();
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Обновлено', $updated['name']);
        $this->assertEquals('в процессе', $updated['status']);

        // 4. Получение всех задач (убедиться, что задача есть в списке)
        $client->request('GET', '/api/tasks', [], [], $headers);
        $this->assertResponseIsSuccessful();
        $list = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(isset($list['tasks']) && is_array($list['tasks']));
        $this->assertNotEmpty(array_filter($list['tasks'], fn($t) => $t['id'] === $taskId));

        // 5. Удаление задачи
        $client->request('DELETE', '/api/tasks/' . $taskId, [], [], $headers);
        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Task successfully deleted', $data['message']);

        // 6. Проверка, что задача удалена
        $client->request('GET', '/api/tasks/' . $taskId, [], [], $headers);
        $this->assertResponseStatusCodeSame(404);
    }
}

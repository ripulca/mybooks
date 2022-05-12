<?php

namespace App\Test\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private BookRepository $repository;
    private string $path = '/book/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Book::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Book index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'book[name]' => 'Testing',
            'book[author]' => 'Testing',
            'book[cover]' => 'Testing',
            'book[file]' => 'Testing',
            'book[last_reading_date]' => 'Testing',
            'book[user_id]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setName('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setCover('My Title');
        $fixture->setFile('My Title');
        $fixture->setLast_reading_date('My Title');
        $fixture->setUser_id('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Book');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setName('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setCover('My Title');
        $fixture->setFile('My Title');
        $fixture->setLast_reading_date('My Title');
        $fixture->setUser_id('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'book[name]' => 'Something New',
            'book[author]' => 'Something New',
            'book[cover]' => 'Something New',
            'book[file]' => 'Something New',
            'book[last_reading_date]' => 'Something New',
            'book[user_id]' => 'Something New',
        ]);

        self::assertResponseRedirects('/book/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getCover());
        self::assertSame('Something New', $fixture[0]->getFile());
        self::assertSame('Something New', $fixture[0]->getLast_reading_date());
        self::assertSame('Something New', $fixture[0]->getUser_id());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Book();
        $fixture->setName('My Title');
        $fixture->setAuthor('My Title');
        $fixture->setCover('My Title');
        $fixture->setFile('My Title');
        $fixture->setLast_reading_date('My Title');
        $fixture->setUser_id('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/book/');
        self::assertSame(0, $this->repository->count([]));
    }
}

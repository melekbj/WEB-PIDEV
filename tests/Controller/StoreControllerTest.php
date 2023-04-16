<?php

namespace App\Test\Controller;

use App\Entity\Store;
use App\Repository\StoreRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StoreControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private StoreRepository $repository;
    private string $path = '/store/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Store::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Store index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'store[nom]' => 'Testing',
            'store[location]' => 'Testing',
            'store[photo]' => 'Testing',
            'store[user]' => 'Testing',
            'store[produit]' => 'Testing',
            'store[categorie]' => 'Testing',
        ]);

        self::assertResponseRedirects('/store/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Store();
        $fixture->setNom('My Title');
        $fixture->setLocation('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setUser('My Title');
        $fixture->setProduit('My Title');
        $fixture->setCategorie('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Store');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Store();
        $fixture->setNom('My Title');
        $fixture->setLocation('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setUser('My Title');
        $fixture->setProduit('My Title');
        $fixture->setCategorie('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'store[nom]' => 'Something New',
            'store[location]' => 'Something New',
            'store[photo]' => 'Something New',
            'store[user]' => 'Something New',
            'store[produit]' => 'Something New',
            'store[categorie]' => 'Something New',
        ]);

        self::assertResponseRedirects('/store/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getLocation());
        self::assertSame('Something New', $fixture[0]->getPhoto());
        self::assertSame('Something New', $fixture[0]->getUser());
        self::assertSame('Something New', $fixture[0]->getProduit());
        self::assertSame('Something New', $fixture[0]->getCategorie());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Store();
        $fixture->setNom('My Title');
        $fixture->setLocation('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setUser('My Title');
        $fixture->setProduit('My Title');
        $fixture->setCategorie('My Title');

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/store/');
    }
}

<?php declare(strict_types=1);

namespace Symplify\ChangelogLinker\Tests\Console\Output;

use Iterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\ChangelogLinker\ChangeTree\Change;
use Symplify\ChangelogLinker\Console\Output\DumpMergesReporter;
use Symplify\ChangelogLinker\Git\GitCommitDateTagResolver;

final class WithTagsTest extends TestCase
{
    /**
     * @var Change[]
     */
    private $changes = [];

    /**
     * @var BufferedOutput
     */
    private $bufferedOutput;

    /**
     * @var DumpMergesReporter
     */
    private $dumpMergesReporter;

    protected function setUp(): void
    {
        $this->bufferedOutput = new BufferedOutput();
        $this->dumpMergesReporter = new DumpMergesReporter(new SymfonyStyle(
            new ArrayInput([]),
            $this->bufferedOutput
        ), new GitCommitDateTagResolver());

        $this->changes = [new Change('[SomePackage] Message', 'Added', 'SomePackage', 'Message', 'me', 'v2.0.0')];
    }

    public function testReportChanges(): void
    {
        $this->dumpMergesReporter->reportChanges($this->changes, true);

        $this->assertStringEqualsFile(__DIR__ . '/WithTagsSource/expected1.md', $this->bufferedOutput->fetch());
    }

    /**
     * @dataProvider provideDataForReportChangesWithHeadlines()
     */
    public function testReportBothWithCategoriesPriority(
        bool $withCategories,
        bool $withPackages,
        bool $withTags,
        string $priority,
        string $expectedOutputFile
    ): void {
        $this->dumpMergesReporter->reportChangesWithHeadlines(
            $this->changes,
            $withCategories,
            $withPackages,
            $withTags,
            $priority
        );

        $this->assertStringEqualsFile($expectedOutputFile, $this->bufferedOutput->fetch());
    }

    public function provideDataForReportChangesWithHeadlines(): Iterator
    {
        yield [true, false, true, 'categories', __DIR__ . '/WithTagsSource/expected2.md'];
        yield [false, true, true, 'categories', __DIR__ . '/WithTagsSource/expected3.md'];
    }
}
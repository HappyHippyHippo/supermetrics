<?php

namespace Tests\unit\module\Statistics\src\Calculator;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostsPerUserPerMonth;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;
use Statistics\Enum\StatsEnum;

/** @coversDefaultClass \Statistics\Calculator\AveragePostsPerUserPerMonth */
class AveragePostsPerUserPerMonthTest extends TestCase
{
    private int $postId;
    private AveragePostsPerUserPerMonth $sut;

    protected function setUp(): void
    {
        $this->postId = 1;
        $this->sut = new AveragePostsPerUserPerMonth();
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testEmptyStatisticsIfNoAccumulateHasBeenCalled(): void
    {
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts');

        $this->setUpCalculatorParameters(6);

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testIgnoreOutOfDatePost(): void
    {
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts');

        $this->setUpCalculatorParameters(1);
        $this->setUpMonthlyPosts(7, [1]);

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testSinglePostInAMonth(): void
    {
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(1)
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        $this->setUpMonthlyPosts(1, [1]);

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testMultipleUserPostsInASingleMonth(): void
    {
        $posts = [1 => [6]];
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(round(array_sum($posts[1]) / count($posts[1])))
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        foreach($posts as $month => $usersPosts) {
            $this->setUpMonthlyPosts($month, $usersPosts);
        }

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testMultipleUsersWithMultiplePostsInASingleMonth(): void
    {
        $posts = [1 => [6, 4, 5]];
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(round(array_sum($posts[1]) / count($posts[1])))
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        foreach($posts as $month => $usersPosts) {
            $this->setUpMonthlyPosts($month, $usersPosts);
        }

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testSinglePostInMultipleMonths(): void
    {
        $posts = [1 => [1], 2 => [1], 3 => [1]];
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(round(array_sum($posts[1]) / count($posts[1])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("February")
                ->setValue(round(array_sum($posts[2]) / count($posts[2])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("March")
                ->setValue(round(array_sum($posts[3]) / count($posts[3])))
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        foreach($posts as $month => $usersPosts) {
            $this->setUpMonthlyPosts($month, $usersPosts);
        }

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testMultiplePostInMultipleMonths(): void
    {
        $posts = [1 => [6], 2 => [2], 3 => [10]];
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(round(array_sum($posts[1]) / count($posts[1])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("February")
                ->setValue(round(array_sum($posts[2]) / count($posts[2])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("March")
                ->setValue(round(array_sum($posts[3]) / count($posts[3])))
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        foreach($posts as $month => $usersPosts) {
            $this->setUpMonthlyPosts($month, $usersPosts);
        }

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @covers ::accumulateData
     * @covers ::doCalculate
     * @throws Exception
     */
    public function testMultiplePostsInMultipleMonthsByMultipleUsers(): void
    {
        $posts = [1 => [6, 3, 9], 2 => [2, 10, 7], 3 => [10, 1, 3]];
        $expected = (new StatisticsTo())
            ->setName('average-posts-per-user')
            ->setUnits('posts')
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("January")
                ->setValue(round(array_sum($posts[1]) / count($posts[1])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("February")
                ->setValue(round(array_sum($posts[2]) / count($posts[2])))
                ->setUnits('posts'))
            ->addChild((new StatisticsTo())
                ->setName('average-posts-per-user')
                ->setSplitPeriod("March")
                ->setValue(round(array_sum($posts[3]) / count($posts[3])))
                ->setUnits('posts'));

        $this->setUpCalculatorParameters(6);
        foreach($posts as $month => $usersPosts) {
            $this->setUpMonthlyPosts($month, $usersPosts);
        }

        $this->assertEquals($expected, $this->sut->calculate());
    }

    /**
     * @param int $numberOfMonths
     * @return void
     * @throws Exception
     */
    protected function setUpCalculatorParameters(int $numberOfMonths): void
    {
        $this->sut->setParameters((new ParamsTo())
            ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
            ->setStartDate(new DateTime("2000-01-01"))
            ->setEndDate(new DateTime(sprintf("2000-%02d-01", $numberOfMonths))));
    }

    /**
     * @param int $month
     * @param array $postCounts is a map of id of author to the number of posts for the given month
     * @return void
     * @throws Exception
     */
    protected function setUpMonthlyPosts(int $month, array $postCounts): void
    {
        foreach ($postCounts as $id => $count) {
            for ($i = 0; $i < $count ; $i++) {
                $this->accumulatePost($id, sprintf("2000-%02d-01", $month));
            }
        }
    }

    /**
     * @param int $authorId
     * @param string $date
     * @return void
     * @throws Exception
     */
    protected function accumulatePost(int $authorId, string $date): void
    {
        $this->sut->accumulateData((new SocialPostTo())
            ->setId($this->postId++)
            ->setAuthorId($authorId)
            ->setAuthorName('author')
            ->setText("post text")
            ->setType("post type")
            ->setDate(new DateTime($date)));
    }
}

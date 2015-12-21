---
date: "2013-06-03T22:00:00+02:00"
Description: "Behat Mink sleep alternative, friendly step definitions and help to structure pages"
Section: posts
Slug: behat-sleep-alternative
Title: "Behat Mink sleep alternative"
---

This post is response to [Matt Waynne's post](http://blog.mattwynne.net/2013/06/03/death-to-sleeps/)
and concentrated on **Behat Mink** example for PHP. In short: sleeps to wait for a state change on test target - is just
a guess, which might not be correct for different circumstances, etc.: slow server or network. The solution proposed in
this post - is about polling for a state change.

<!--more-->

**Last update date: 2013-06-03**

**NOTE:** these behat tips do not cover installation of behat, refer to [this link](http://behat.org/), it is suitable
for developers familiar with behat and mink

## Usual pattern of slow failure

I've seen a lot of **Behat** features, having a step definition

    When I press button "Save"
    And I wait for 7 seconds
    Then I should see a success notification "Your profile was successfully updated."

If your server updates in 1 second, test suite will be waiting for nothing. If it fails, because response arrives in
more than 7 seconds, then your suite fails, no matter if the cause was just a slow network problem. You cannot guess how
long will it take for an action to finish. As well, **sleep** is not a part of the business logic, which should be visible in
definition of a feature and it's scenarios.

## An alternative

Poll for a state in a loop, within max number of retries. Here is an implementation in MinkContext of the step
definitions above:

    <?php

    use Behat\MinkExtension\Context\MinkContext;
    use Behat\Mink\Element\TraversableElement;

    class FeatureContext extends MinkContext {

        /**
         * @Then /^I should see an? (success|error) notification "([^"]*)"$/
         */
        function iShouldSeeTypeNotificationMessage($type, $message) {
            assertNotNull($this->find(
                'xpath',
                '//div[contains(@class, "notification-message") and contains(@class, "'.$type.'") and contains(., "'.$message.'")]'
            ), "Notification of type '$type' with message '$message' was not found on page");
        }

        /**
         * Try to execute $callback with session as argument
         * for $retries with $sleep until a valid result or
         * false value.
         *
         * Used mainly to wait for elements in page while it loads.
         *
         * @param Closure $callback - callback to evaluate
         * @param integer $retries - max number of retries
         * @param float $sleep - sleep between every retry
         *
         * @return result of callback if valid, or false
         */
        public function tryCallback(\Closure $callback, $retries = 10, $sleep = 1)
        {
            static $supportsVisibilityCheck;
            $result = false;
            if ($supportsVisibilityCheck === null) {
                $refl = new \ReflectionMethod($cl = get_class($this->getSession()->getDriver()), 'isVisible');
                $supportsVisibilityCheck = $refl->getDeclaringClass()->getName() === $cl;
            }

            do {
                $result = $callback($this->getSession(), $supportsVisibilityCheck);
            } while (!$result && --$retries && sleep($sleep) !== false);
            return $result;
        }

        /**
         * Find an element matching $cond of $type withing $retries
         *
         * @param string $type - 'css', 'xpath'
         * @param string $cond - condition based on $type, css or xpath expression
         * @param integer $retries - max number of retries
         * @param float $sleep - sleep between every retry
         *
         * @return false or the first result of $cond expression
         */
        public function find($type, $cond, TraversableElement $parent = null, $retries = 10, $sleep = 1) {
            return $this->tryCallback(function($s, $svc) use ($type, $cond, $parent) {
                $parent = $parent ?: $s->getPage();
                if ($el = $parent->find($type, $cond)) {
                    if (!$svc || ($svc && $el->isVisible())) {
                        return $el;
                    }
                }
                return null;
            }, $retries, $sleep);
        }
    }

There is a find method, which in turn tries a given condition within a number of retries. Now a sleep step can be
removed and we should not worry about how many seconds it will take for server to generate a resonse. Of course there is
a limit of **retries** and a time to sleep within each retry.

We expect that an element will occur and it will in most of the cases, only on failure a test will wait for all attempts
to find an element. So now our steps look like:

    When I press button "Save"
    Then I should see a success notification "Your profile was successfully updated."

Nothing has changed, except the unrelated to business logic - **wait** step.

## Taking it seriously

You can go a lot further with this, a common case is to wait for a page load:

    Given I am on "/blog-posts"
    When I wait for 3 seconds
    Then I should see "Blog Posts" in the "h1" element

Well, that is very ugly. But there are many devs getting over it.

First of all, we should not use wait and we should not use steps, which contain direct html element references. This
cannot be understandable by a normal person - in other words an user who uses the site being tested.

Second, your page should contain only one **h1** element and it should describe a page. Given these practices, behat can
help to maintain these web standards as well. Lets add a more friendly step definition for **h1** check.

    <?php
    /**
     * @Then /^I should see "([^"]+)" on page headline$/
     */
    function iShouldSeeTextOnPageHeadline($text) {
        assertNotNull($this->find('xpath', '//h1[contains(., "'.$text.'")]'), "Text '$text' was not found on page headline");
    }

This would make steps look nicer now:

    Given I am on "/blog-posts"
    Then I should see "Blog Posts" on page headline

Working with explicit url addresses might as well be not that straight forward. It may be more readable to use page
headlines as a factor, that can also help to structure page headlines and SEO better:

    Given I am on page "Blog Posts"

And the context would look like:

    <?php
    /**
     * @Given /^I'm on page "([^"]+)"$/
     * @Given /^I am on page "([^"]+)"$/
     * @Given /^I visit page "([^"]+)"$/
     */
    function iAmOnPage($page) {
        $this->visit($this->getPath($page));
        $this->iShouldSeeTextOnPageHeadline($page);
    }

    /**
     * Overrides mink path locator to allow urls by page headlines as well
     * {@inheritdoc}
     */
    public function locatePath($path) {
        return parent::locatePath(strpos($path, '/') === 0 ? $path : $this->getPath($path));
    }

    private function getPath($page) {
        switch ($page) {
            case 'Blog Posts':
                return '/blog-posts'; // some router in usual cases
            default:
                throw new PendingException("The page '$page' is not available");
        }
    }

These are just a few tips on how to get the feature step definitions more concise and readable to any user who
might see it.


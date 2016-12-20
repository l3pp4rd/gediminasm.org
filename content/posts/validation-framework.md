---
date: "2016-12-20T19:55:00+02:00"
Description: "Guide to build flexible and simple validation framework with Java 8"
Section: posts
Slug: your-java-validation-framework
Title: "Your Java validation framework"
---

If you have ever used **javax.validation** and friends, you have probably noticed
a few of these problems:

- how cumbersome it becomes with validation groups.
- since it is a world of web APIs, you want only the first error.
- trashing domain objects with annotations.
- restricting validated objects to certain use cases.
- complexity of validator implementation and a context class to keep it all together.

We have **lambdas** now in Java8, which makes it possible to embrace the power of
simple interface and composition.

<!--more-->

If I caught your attention, lets start with a simple interface for our Validator. The smaller
the interface - the stronger it is.

    @FunctionalInterface
    public interface Validator {

        /**
         * Validates and maybe returns an error message
         *
         * @return maybe error message
         */
        @NotNull Optional<String> valid();
    }

We could have used `@Nullable String` instead of Optional, but it gives better functional
implementation choices. The `@NotNull` annotation here is from jetbrains, convenient for integrity checks.
Now in order to compose validators, we will create another class.

    public final class Group implements Validator {

        private final @NotNull Collection<Validator> validators;

        public Group(final @NotNull Validator... validators) {
            this.validators = Arrays.asList(validators);
        }

        @Override
        public @NotNull Optional<String> valid() {
            return validators.stream()
                    .map(Validator::valid)
                    .filter(Optional::isPresent)
                    .map(Optional::get)
                    .findFirst();
        }
    }

This simply allows us to compose validators and return the first error available.
Now in order to have something to play with, we can add a few very generic validators.
First goes `NotNullValidator`.

    public final class NotNullValidator implements Validator {

        private final String message;

        public NotNullValidator(final Object object) {
            this.message = object != null ? null : "cannot be null";
        }

        @Override
        public @NotNull Optional<String> valid() {
            return Optional.ofNullable(message);
        }
    }

And finally the `SizeValidator`.

    public class SizeValidator implements Validator {

        private final String message;

        private SizeValidator(final String message) {
            this.message = message;
        }

        @Override
        public @NotNull
        Optional<String> valid() {
            return Optional.ofNullable(message);
        }

        public static <T extends Comparable<T>> @NotNull Validator range(
                final @Nullable T val,
                final @NotNull T min,
                final @NotNull T max
        ) {
            if (val == null) {
                return new SizeValidator(null);
            }

            if (val.compareTo(min) == -1) {
                return new SizeValidator(String.format("size is below minimum required %s", min.toString()));
            }

            if (val.compareTo(max) == 1) {
                return new SizeValidator(String.format("size is above maximum allowed %s", max.toString()));
            }

            return new SizeValidator(null);
        }

        public static @NotNull Validator range(final Collection<?> val, final int min, final int max) {
            if (val == null) {
                return new SizeValidator(null);
            }

            return range(val.size(), min, max);
        }

        public static @NotNull Validator range(final String val, final int min, final int max) {
            if (val == null) {
                return new SizeValidator(null);
            }

            return range(val.length(), min, max);
        }
    }

It is simple enough to add `min` and `max` methods apart from `range`. Although some other types
could be useful. But for proof of concept that will be enough.

So now we can write our test. Given we have an User class as our validated domain subject.

    public final class User {
        private final String username;
        private final Set<String> roles;

        public User(final String username, final Set<String> roles) {
            this.username = username;
            this.roles = new HashSet<>(roles);
        }

        public String username() {
            return username;
        }

        public Set<String> roles() {
            return roles;
        }
    }

Lets write a test case now:

    public class UserTest {

        private static Validator userValidator(final User user) {
            // null value will validate
            return user == null ? Optional::empty : new Group(
                    // username cannot be null
                    new NotNullValidator(user.username()),
                    // username size is between 1 and 16
                    () -> SizeValidator.range(user.username(), 1, 16).valid(),
                    // roles cannot be null
                    new NotNullValidator(user.roles()),
                    // user must have at least one role
                    () -> SizeValidator.range(user.roles(), 1, Integer.MAX_VALUE).valid()
            );
        }

        @Test
        public void shouldPassOnValidUser() {
            final User user = new User("gediminas", new HashSet<>(Arrays.asList("admin", "guest")));

            assertThat(userValidator(user).valid(), isEmpty());
        }

        @Test
        public void shouldFailIfUsernameLengthIsAboveValid() {
            final User user = new User(String.format("%17d", 1), null);

            assertThat(userValidator(user).valid(), hasValue("size is above maximum allowed 16"));
        }
    }

I've added some comments to explain validators composed. There can be different use cases to validate user, like for
registration process, for password reminder and whatever. If a nested validator depends on previous one like NotNull
checker, then we use **lambda** expression to prevent too early evaluation, in this case it was not necessary.

Furthermore, you probably would like to have a field path with an error message right? Lets make it without
a need to extend the interface. Here is the Field class:

    public final class Field implements Validator {

        private final Validator validator;
        private final String field;

        private Field(final String field, final Validator validator) {
            this.validator = validator;
            this.field = field;
        }

        @Override
        public @NotNull Optional<String> valid() {
            return validator.valid().map(this::message);
        }

        public static Validator of(final @NotNull String field, final @NotNull Validator validator) {
            return new Field(field, validator);
        }

        private @NotNull String message(final @NotNull String msg) {
            if (!msg.startsWith("\"")) {
                return String.format("\"%s\" %s", field, msg);
            }
            return String.format("\"%s.%s", field, msg.substring(1));
        }
    }

Since only Field validator mapper knows about paths, it can construct them by following a very
basic rule. And now if we update our test:

    public class UserTest {

        private static Validator userValidator(final User user) {
            return user == null ? Optional::empty : new Group(
                    Field.of("username", new Group(
                            new NotNullValidator(user.username()),
                            SizeValidator.range(user.username(), 1, 16)
                    )),
                    Field.of("roles", new Group(
                            new NotNullValidator(user.roles()),
                            SizeValidator.range(user.roles(), 1, Integer.MAX_VALUE)
                    ))
            );
        }

        @Test
        public void shouldPassOnValidUser() {
            final User user = new User("gediminas", new HashSet<>(Arrays.asList("admin", "guest")));

            assertThat(userValidator(user).valid(), isEmpty());
        }

        @Test
        public void shouldFailIfUsernameLengthIsAboveValid() {
            final User user = new User(String.format("%17d", 1), null);

            assertThat(userValidator(user).valid(), hasValue("\"username\" size is above maximum allowed 16"));
        }
    }

If you nest more Field validators by using for example another subclass validator used as a property, then a path
will be built for a field name.

This gives a powerful, flexible and yet simple validation framework. If you like, you can
[download the code](/files/validation.zip) used for
this example project. In the end, it can easily be modified to match different needs. Though, the purpose of this was
to show how you can embrace the composition with java 8 lambdas and a strong interface.


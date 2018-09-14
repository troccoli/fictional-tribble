# Shopworks Recruitment Test


## Technical Questions

### How long did you spend on the coding test? What would you add to your solution if you had more time?

To complete my work took me 4 hours and 45 minutes of work. This does not include the breaks I took, about 10 minutes 
every hour and 1 hour for lunch.

I definitely would have liked to add more tests. I would also have like to add some validation for the data maybe. And
given how the tests were written in the brief I would have liked to write them in gherkin and implement them in behat.

Maybe some more refactoring. I'm not too happy with the naming of my service classes. I would have possibly introduced
some contracts to make the code more portable.

### Why did you choose PHP as your main programming language?

I come form a stardad C background and when I decided to move into web development PHP seems the logical choice as
the syntax and semantic is very similar to C (or at least it was). I had also use it before for some simple scripting.

### What is your favourite thing about Laravel? 

There are many. The first thing that attracted me to Laravel was how easy and simple it was to learn (I was using 4.2).
Everything seems to be very logical and, in my opinion, it does what a framework should do: do a lot of the usual work
for you so you can concentrate on the business logic. But, if I had to pick one thing I would probably say validation.
Maybe because I came from ZF1 (where is was a nightmare), but validating data in Laravel is so easy and intuitive. I mean,
it's just an array of rules, it can't be simpler than that.

### What is your least favourite?

This is a tough one. Maybe the Policy and Gate. I find that they is some overlap between the two, and it's not always
easy to decide which one to use. Having said that, it's probably not too bad as it allows developers to choose what
best fit their business and what they feel more comfortable with.

### How would you track down a performance issue in production? Have you ever had to do this?

The only performance issues I found in my career where related to MySQL. In those cases, the DebugBar package is out of
questions (but it's extremely helpful in development where you can quickly find out if your code calls the same SQL over
and over for example). You have then have to revert to MySQL tools like the slow query log and the log itself. It's not
ideal to have the slow query log enabled in production or to log a log of details in the log, so it must be done only
during investigation.
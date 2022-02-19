# Subscription Schedule Payment <Badge text="new in v6.0" type="tip"/>
This is the job that needs to be called to process a subscription scheduled change and payment. This job does not check 
if the schedule is due (that is done in the (Subscription Payment Queuer Job)).

## What it does
This job passes data to and calls `execute()` method on the payment method service.

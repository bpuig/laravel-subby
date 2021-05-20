var sidebar = [
    {
        title: 'The package',   // required
        sidebarDepth: 1,
        collapsable: false,
        children: [
            ['', 'Introduction'],
            ['install/', 'Installation']
        ]
    },
    {
        title: 'Usage',   // required
        sidebarDepth: 2,
        collapsable: false,
        children: [
            ['models/', 'Models'],
            ['models/plan-model.md', 'Plan Model'],
            ['models/plan-feature-model.md', 'Plan Feature Model'],
            ['models/plan-subscription-model.md', 'Plan Subscription Model']
        ]
    },
    {
        title: 'Extras',   // required
        sidebarDepth: 3,
        collapsable: false,
        children: [
            ['extras/plan-subscription-schedule.md', 'Plan Subscription Schedule'],
        ]
    },
]

module.exports = {sidebar}

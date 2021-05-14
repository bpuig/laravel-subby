var sidebar = [{
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
            ['models/plan-subscription-model.md', 'Plan Subscription Model'],
            ['models/plan-subscription-feature-model.md', 'Plan Subscription Feature Model'],
        ]
    }]

module.exports = {sidebar}

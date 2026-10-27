<?php
/**
 * Demo seed content. Every headline and paragraph here is original placeholder
 * copy written for the prototype; nothing is taken from ndtv.com.
 *
 * Videos are not seeded from here: they come from NDTV's own feed via the
 * video sync (inc/video-sync.php, data/ndtv-video-feed.json).
 *
 * Story rows: [ section, kicker, title, dek, tags, extras ].
 *
 * @package NDTV_Core
 */

defined( 'ABSPATH' ) || exit;

return array(
	'authors'    => array(
		array( 'ananya-rao', 'Ananya Rao', 'Senior Correspondent, Delhi. Covers infrastructure, railways and the urban environment.' ),
		array( 'kabir-sethi', 'Kabir Sethi', 'Sports Editor. Has covered six cricket World Cups and still keeps a paper scorebook.' ),
		array( 'meera-pillai', 'Meera Pillai', 'Business Correspondent, Mumbai. Markets, banking and the startup economy.' ),
		array( 'arjun-malhotra', 'Arjun Malhotra', 'Technology and Science Editor. Writes about AI, space and the gadgets in your pocket.' ),
		array( 'farah-qureshi', 'Farah Qureshi', 'Features Writer. Entertainment, lifestyle and the stories that make you smile.' ),
	),

	// slug => [ name, author login ].
	'sections'   => array(
		'india'         => array( 'India', 'ananya-rao' ),
		'world'         => array( 'World', 'ananya-rao' ),
		'business'      => array( 'Business', 'meera-pillai' ),
		'sports'        => array( 'Sports', 'kabir-sethi' ),
		'entertainment' => array( 'Entertainment', 'farah-qureshi' ),
		'lifestyle'     => array( 'Lifestyle', 'farah-qureshi' ),
		'health'        => array( 'Health', 'ananya-rao' ),
		'tech'          => array( 'Tech', 'arjun-malhotra' ),
		'education'     => array( 'Education', 'ananya-rao' ),
		'auto'          => array( 'Auto', 'meera-pillai' ),
		'science'       => array( 'Science', 'arjun-malhotra' ),
		'offbeat'       => array( 'Offbeat', 'farah-qureshi' ),
	),

	// Listed newest first.
	'stories'    => array(
		array( 'india', 'Monsoon Watch', 'Coastal Districts On Alert As Late-Season Depression Nears Odisha', 'Fishing has been suspended along a 300-km stretch and schools in three districts will stay shut on Tuesday.', array( 'monsoon', 'weather' ), array( 'lead' => true ) ),
		array( 'business', 'Markets', 'Sensex Ends 600 Points Higher As Banking Stocks Rally', 'Private lenders led the gains after strong quarterly loan growth numbers.', array( 'markets' ) ),
		array( 'sports', '3rd T20I:', 'Openers Put On 180 As India Take Unassailable 3-0 Series Lead', 'The hosts were left chasing shadows as the target was reached with 21 balls to spare.', array( 'cricket' ), array( 'scorecard' => true ) ),
		array( 'tech', '', 'Indian Language AI Models Get A Boost With New Open Dataset', 'The release covers speech and text in all 22 scheduled languages.', array( 'ai' ) ),
		array( 'india', '', 'Railways To Add 1,200 Festive Special Trains Ahead Of Chhath Rush', 'Extra coaches and holding areas at major junctions are planned for the two-week peak.', array( 'railways', 'festivals' ) ),
		array( 'entertainment', 'Box Office', 'Festive Release Crosses ₹100 Crore In Five Days', 'Trade analysts credit strong word of mouth in smaller towns.', array( 'bollywood', 'festivals' ) ),
		array( 'health', '', 'Dengue Cases Rise In Three Metros, Doctors Urge Early Testing', 'Hospitals are adding fever clinics for the post-monsoon surge.', array( 'dengue', 'monsoon' ) ),
		array( 'world', '', 'Leaders Meet In Geneva For Third Round Of Climate Finance Talks', 'Developing nations are pushing for a firm number on adaptation funding.', array( 'climate' ) ),
		array( 'business', '', 'Gold Prices Ease From Record High Ahead Of Dhanteras Buying', 'Jewellers expect volumes to hold up despite elevated rates.', array( 'gold', 'festivals' ) ),
		array( 'india', 'Explained', 'What The New Draft Data Protection Rules Mean For Your Phone', 'Consent notices, deletion timelines and what changes for apps that collect your location.', array( 'tech-policy' ) ),
		array( 'sports', '', 'Young Pace Attack Earns Praise After Clean Sweep In Wellington', 'Three bowlers shared 24 wickets across the series.', array( 'cricket' ) ),
		array( 'auto', 'First Drive', 'New Compact Electric SUV Promises 450 km Range', 'We spent a day with it on city roads and the expressway.', array( 'ev' ) ),
		array( 'lifestyle', 'Festive Edit', 'Handloom Sarees Make A Comeback In This Year\'s Diwali Shopping', 'Weavers\' cooperatives report their best season in years.', array( 'festivals', 'fashion' ) ),
		array( 'india', '', 'Delhi Air Quality Slips To "Very Poor" As Winds Ease Over Capital', 'Construction curbs under the graded response plan could return this week.', array( 'air-quality', 'delhi' ) ),
		array( 'education', 'Exams', 'Board Exam Date Sheet Released: Key Dates For Class 10 And 12', 'Practical exams will begin in the first week of February.', array( 'exams' ) ),
		array( 'tech', 'Review', 'Mid-Range Phones Under ₹25,000: Which One Should You Buy?', 'We tested battery, camera and display on five popular models.', array( 'gadgets' ) ),
		array( 'world', 'Analysis', 'Why Shipping Costs Are Rising Again On Asia-Europe Routes', 'Rerouted vessels and port congestion are adding days to delivery times.', array( 'trade' ) ),
		array( 'sports', 'ISL', 'Late Header Keeps Kerala Side Top Of The Table', 'A 92nd-minute winner sealed a fourth straight victory.', array( 'football' ) ),
		array( 'entertainment', '', 'Streaming Platforms Bet Big On Regional Thrillers This Winter', 'Six new series in Tamil, Marathi and Bengali are lined up.', array( 'streaming' ) ),
		array( 'business', 'Earnings', 'Mid-Sized IT Firms Beat Estimates On Strong Deal Wins', 'Analysts flag AI services as the fastest-growing segment.', array( 'ai', 'earnings' ) ),
		array( 'health', '', 'Walking 7,000 Steps A Day May Be Enough, Says New Study', 'Researchers tracked 50,000 adults over a decade.', array( 'wellness' ) ),
		array( 'science', '', 'ISRO Readies Next Earth Observation Satellite For Launch', 'The satellite will track crop health and soil moisture.', array( 'space' ) ),
		array( 'offbeat', 'Viral', 'Elephant Herd\'s Road Crossing Halts Traffic For 40 Minutes', 'Forest staff guided the herd of 18 safely across.', array( 'wildlife' ) ),
		array( 'india', '', 'High-Speed Rail Corridor Completes Longest Viaduct Section In Gujarat', 'Engineers say track-laying on the stretch can begin early next year.', array( 'railways', 'infrastructure' ) ),
		array( 'tech', '', 'UPI Crosses 20 Billion Monthly Transactions For The First Time', 'Small-value payments under ₹500 make up most of the growth.', array( 'payments' ) ),
		array( 'lifestyle', '', 'Five Winter Skincare Habits Dermatologists Actually Recommend', 'Small changes that help skin cope with dry, polluted air.', array( 'wellness', 'air-quality' ) ),
		array( 'auto', '', 'Car Sales Hit Festive-Season Record In October', 'Waiting periods are back for popular hybrid models.', array( 'festivals' ) ),
		array( 'sports', '', 'Indian Doubles Pair Reaches Quarter-Finals In Paris', 'The duo saved two match points in the deciding set.', array( 'tennis' ) ),
		array( 'entertainment', '', 'Classic 1990s Romance Returns To Theatres In Restored 4K', 'Re-release screenings in 30 cities sold out within hours.', array( 'bollywood' ) ),
		array( 'business', '', 'Startup Funding Rebounds In Third Quarter, Led By Climate Tech', 'Early-stage rounds made up more than half of all deals.', array( 'startups', 'climate' ) ),
		array( 'world', '', 'Indian Diaspora Groups Plan Diwali Events Across 40 Cities Abroad', 'Organisers expect record turnout at public celebrations this year.', array( 'festivals' ) ),
		array( 'education', '', 'Universities Add AI Literacy To First-Year Curriculum', 'The course covers tools, limits and academic integrity.', array( 'ai' ) ),
		array( 'health', 'Explained', 'What High AQI Does To Your Lungs, And How To Protect Them', 'Masks, purifiers and when to skip outdoor exercise.', array( 'air-quality' ) ),
		array( 'science', '', 'Researchers Spot New Frog Species In Western Ghats', 'The tiny amphibian is smaller than a fingernail.', array( 'wildlife' ) ),
		array( 'tech', 'AI', 'Newsrooms Test AI Tools For Translation And Transcripts', 'Editors say human review remains essential.', array( 'ai' ) ),
		array( 'offbeat', '', 'Village Library Run From A Converted Bus Wins National Award', 'Volunteers lend 3,000 books a month across 12 villages.', array() ),
		array( 'sports', '', 'National Hockey Camp Opens With 33 Probables Ahead Of Pro League', 'The coach said fitness benchmarks will decide the final squad.', array( 'hockey' ) ),
		array( 'auto', '', 'Highway Toll Plazas To Move To Number-Plate Scanning Pilot', 'Barrier-free tolling is being trialled on two corridors.', array( 'infrastructure' ) ),
		array( 'lifestyle', '', 'How To Build A Home Workspace That Works In A Small Flat', 'Light, storage and a good chair matter more than gadgets.', array() ),
		array( 'entertainment', 'Interview', '"We Shot The Climax In One Take": Director On Her Debut Feature', 'The first-time filmmaker talks about casting non-actors.', array( 'bollywood' ) ),
		array( 'business', '', 'EV Two-Wheeler Sales Cross 1 Lakh Units In A Single Month', 'Price cuts and festive offers drove the jump.', array( 'ev' ) ),
		array( 'education', '', 'Scholarship Portal Deadline Extended By Two Weeks', 'Students can now apply until mid-November.', array() ),
		array( 'world', '', 'Earthquake Of Magnitude 5.8 Shakes Remote Pacific Islands, No Tsunami Warning', 'Local officials report minor damage to two jetties.', array() ),
		array( 'health', '', 'Government Expands Free Cancer Screening To 100 More Districts', 'Mobile vans will cover rural areas first.', array() ),
		array( 'science', '', 'Why This Year\'s Winter Could Be Warmer Than Usual', 'Ocean temperatures are shifting seasonal patterns.', array( 'climate', 'weather' ) ),
		array( 'tech', '', 'Satellite Broadband Trials Begin In Hill States', 'Early users report speeds good enough for video calls.', array( 'internet' ) ),
		array( 'offbeat', '', 'Bengaluru Café Hires Retired Teachers As Weekend Baristas', 'Customers come for the coffee and stay for the stories.', array() ),
		array( 'lifestyle', '', 'The Rise Of Millet Breakfasts In Urban Kitchens', 'Ragi dosa and jowar upma are moving from tradition to trend.', array( 'food' ) ),
		array( 'auto', '', 'Motorcycle Makers Bet On 350cc Segment For Growth', 'Three launches are planned before the year ends.', array() ),
		array( 'education', '', 'Rural Schools Report Gains After Two Years Of Digital Classrooms', 'Attendance and maths scores both improved.', array() ),
		array( 'entertainment', '', 'Music Festival Lineup Announced For Goa Edition In December', 'Forty artists across four stages are confirmed.', array( 'music' ) ),
	),

	// Section-flavoured body copy, shared by every story in a section.
	'bodies'     => array(
		'default' => array(
			'Officials said a detailed update would follow later in the day, and that the situation was being reviewed every few hours.',
			'People familiar with the plans said the next steps would depend on feedback gathered over the coming week.',
			'Experts NDTV spoke to said the move was expected, but that the timing and scale had taken many by surprise.',
		),
		'sports'  => array(
			'The captain said the dressing room had stayed calm through the tight phases of the game and credited the support staff for their planning.',
			'Selectors are expected to keep the core of the squad together for the next series, with one or two changes to manage workloads.',
			'Fans who travelled for the match stayed back long after the final ball, and the players walked a lap of the ground to thank them.',
		),
		'business' => array(
			'Analysts said the numbers were broadly in line with expectations, though the outlook for the next two quarters remained cautious.',
			'Retail investors have been active through the festive season, with new demat accounts rising for a third straight month.',
			'Market watchers will now look to global cues and the central bank\'s commentary at its next policy meeting.',
		),
	),

	// Video categories: name => description shown on the /video/ hub.
	'video_cats' => array(
		'News'          => 'Bulletins, explainers and on-the-ground reports from across India and the world, around the clock.',
		'Shorts'        => 'The day\'s stories in under a minute, made for your phone.',
		'Sports'        => 'Match analysis, highlights and conversations from cricket, football, tennis and more.',
		'Business'      => 'Markets, money and the economy, explained by the NDTV Profit desk.',
		'Entertainment' => 'Box office, streaming, music and the people behind the screen.',
		'Auto'          => 'First drives, comparisons and buying advice for cars, bikes and EVs.',
		'Exclusive'     => 'Reporting you will only see on NDTV.',
		'Features'      => 'Longer stories on science, technology, culture and the outdoors.',
	),
);

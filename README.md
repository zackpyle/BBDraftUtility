# BBDraftUtility
Provides utilities for Beaver Builder drafts, including scheduling and draft notices

## Draft Notices
You will be notified that there is a saved Beaver Builder draft in the following ways:<br>
1. A modal will pop up when you open Beaver Builder:<br>
![Screenshot 2024-08-25 at 07 22 23](https://github.com/user-attachments/assets/64b8df9a-dce3-40da-bd1b-4cbe233dfadf)
2. In the post list it will say Saved Draft: <br>
![Screenshot 2024-08-25 at 08 09 35](https://github.com/user-attachments/assets/3339e053-7e69-45b2-a0e3-acacb35c2130)
3. In the post's edit screen, it will display this warning:<br>
<sup><sub>(Respects white label naming)</sub></sup><br>
![Screenshot 2024-08-25 at 07 20 18](https://github.com/user-attachments/assets/c77fcbdf-9a19-4377-a4cb-c9b2023b5111)
4. The green Beaver Builder status dot will be yellow instead of green in the row actions<br>
![Screenshot 2024-08-25 at 08 09 35](https://github.com/user-attachments/assets/8d3203be-3847-4fc1-b109-b61027608a7a)
<br>and in the admin bar<br>
![Screenshot 2024-08-25 at 08 19 36](https://github.com/user-attachments/assets/f60ecdd0-98bd-40a4-a73c-09150b0a9e76)


## Schedule Drafts to Publish
There is a filter to disable the draft scheduling feature if you don't want this feature<br>
`add_filter( 'bb_draft_utility_enable_scheduling', '__return_false' );`

You can click on the Saved Draft link from the post list to bring up a modal to schedule a date/time for your draft to go live. You can also unschedule and delete drafts from that modal as well.<br>
<sup><sub>(Respects white label naming)</sub></sup><br>
![Screenshot 2024-08-25 at 07 26 41](https://github.com/user-attachments/assets/f14864ac-0fa9-44c9-abe2-9a132fac7395)

After you schedule a draft, a calendar icon will appear next to the Saved Draft link. The scheduled date/time will appear in the modal and when you hover over the calendar icon.<br>
![Screenshot 2024-08-25 at 08 22 33](https://github.com/user-attachments/assets/c9887f2e-2e33-493d-8cbe-278d06ba266e)



Inspired by <a href="https://gist.github.com/Pross/0b517612bb1d1dfb17083b9b32628b82">@pross's gist</a>

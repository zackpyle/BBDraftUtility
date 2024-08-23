# BBDraftUtility
Provides utilities for Beaver Builder drafts, including scheduling and draft notices

## Draft Notices
You will be notified that there is a saved Beaver Builder draft in the following ways:
1. A modal will pop up when you open Beaver Builder:
![Screenshot 2024-08-23 at 15 49 04](https://github.com/user-attachments/assets/94c588f6-3339-4ec6-8aea-3888b9b4a8f2)
2. In the post list it will say Unpublished Changes: 
![Screenshot 2024-08-21 at 13 22 30](https://github.com/user-attachments/assets/e8e9da43-8602-43d5-9f5f-11a6161d3657)
3. In the post's edit screen, it will display this warning:<br>
<sup><sub>(Respects white label naming)</sub></sup><br>
![Screenshot 2024-08-21 at 13 23 44](https://github.com/user-attachments/assets/7c9fff7f-3663-402b-a280-aa07b33c6062)
4. The green Beaver Builder status dot will be yellow instead of green in the row actions
![Screenshot 2024-08-21 at 13 22 30](https://github.com/user-attachments/assets/98a6aae3-c089-4b2b-89ea-fd0f56e6dd94)
<br>and in the admin bar<br>
![Screenshot 2024-08-21 at 13 25 02](https://github.com/user-attachments/assets/5a4db617-d93e-4590-8b3d-7066d4ae21a5)

## Schedule Drafts to Publish
There is a filter to disable the draft scheduling feature if you don't want this feature<br>
`add_filter( 'bb_draft_utility_enable_scheduling', '__return_false' );`

You can click on the Unpublished Changes link from the post list to bring up a modal to schedule a date/time for your draft to go live. You can also unschedule and delete drafts from that modal as well.
![Screenshot 2024-08-21 at 13 22 43](https://github.com/user-attachments/assets/39d7b6f2-c9fa-452c-a130-d2ed600b84ea)

After you schedule a draft, a calendar icon will appear next to the Unpublished Changes link. The scheduled date/time will appear in the modal and when you hover over the calendar icon.
![Screenshot 2024-08-21 at 13 23 27](https://github.com/user-attachments/assets/67607031-dd25-484e-bdfc-3f0bd5500179)


Inspired by <a href="https://gist.github.com/Pross/0b517612bb1d1dfb17083b9b32628b82">@pross's gist</a>

## General Summary
This index.php file is a dynamic webpage with two primary functions:

As a file upload tool, allowing users to upload a "Peer Teaching Observation Sheet" to the server.

As an information portal, which automatically displays the latest news from the sibermu.ac.id website.

The page is designed with a modern aesthetic, using a "glassmorphism" effect and an animated gradient background for a visually engaging user experience.

## Detailed Functionality
1. Initial Setup & News Fetching
The first section of the PHP code performs several key tasks before the page is rendered:

session_start(): Starts a PHP session, which is crucial for temporarily storing notification messages (like "File Accepted!") so they are not lost when the page redirects.

date_default_timezone_set('Asia/Jakarta'): Sets the timezone to Western Indonesia Time (WIB) to ensure all date and time information is accurate.

News Fetching Logic: This code automatically:

Fetches data from the XML sitemap at https://sibermu.ac.id/post-sitemap.xml.

Parses the XML data to extract information for the 4 latest news articles, including the title, link, image URL, and publication date.

Stores all this information into an array named $news_items to be displayed later on the page.

2. File Upload Logic
The code block beginning with if ($_SERVER['REQUEST_METHOD'] === 'POST') runs only when a user submits the form by clicking the "Upload Now" button.

Validation: The code checks if a file was successfully uploaded to the server's temporary location without errors.

File Transfer: If valid, move_uploaded_file() transfers the file from its temporary location to the permanent /uploads folder.

Notifications: A success ("Accepted!") or failure ("Rejected!") message is stored in the $_SESSION.

PRG Pattern (Post-Redirect-Get): After processing, header("Location: ...") and exit() are used to redirect the user back to the same page. This is a best practice that prevents the same file from being accidentally re-uploaded if the user refreshes the page.

3. Helper Functions
Two custom PHP functions are included to help format the display:

getFileIconClass($filename): Analyzes a file's extension (e.g., .pdf, .docx, .jpg) and returns the appropriate Bootstrap icon class name, allowing each file type to have a distinct visual icon.

formatBytes($bytes): Converts a file size from bytes (which is hard to read, e.g., 500000) into a more human-readable format (e.g., "488.28 KB").

4. Page Display (HTML & Frontend)
This section includes all the code after <!DOCTYPE html> and is responsible for what the user sees in their browser.

Design & Styling: Uses Bootstrap 5 and custom CSS to create the modern look, including the animated background, glass effect, and interactive buttons.

Upload Form: Provides the interface for users to select a file from their computer and submit it.

Uploaded File List:

This section dynamically reads all files present in the /uploads directory.

It sorts the files by time, from the oldest uploaded to the newest, using the filemtime() and asort() functions.

It then displays the sorted list, complete with a manual sequence number, icon, file name, upload time, and file size.

Latest News Section:

Checks if the $news_items array (from step 1) contains any data.

If it does, it displays 4 news cards, each with an image, date, and a title that links to the original article on the SiberMu website.

Footer: Displays credit information at the bottom of the page with clickable links.
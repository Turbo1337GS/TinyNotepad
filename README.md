# TinyNotepad

# Notes!

This is a PHP code that handles a note-taking system. Below is a description of each part of the code:

### Configuration

At the beginning of the code, there are configuration variables such as the API key, database connection details, and PHPMailer configuration for sending email messages.

### Database Connection

The code connects to a local MySQL database using the provided authentication details. If the connection is not established, an error message is displayed.

### Request Handling

The code checks the parameters passed in the URL and performs appropriate operations based on the value of the "op" parameter. If the "op" parameter has the value "delete", a note with the specified identifier is deleted. If the "op" parameter has the value "edit", the data of the note with the specified identifier is retrieved for editing.

### Saving and Editing Notes

The code handles saving and editing notes. After submitting a form with note data, the data is validated and saved in the database. If there are any errors, corresponding error messages are displayed.

### Sending Email Messages

After saving or editing a note, the code sends an email message with a notification of the change. Emails are sent using PHPMailer, and the recipient addresses are defined in the code.

### User Interface

At the end of the code, there is HTML code that defines the user interface. It is a simple form that allows entering and editing note data.

Additionally, the page includes external CSS libraries and JavaScript scripts for styling and interface functionality.

All dependencies are linked externally, so the code can be easily run on any PHP server with internet access.


# Demo 

[![Watch the video](https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRNNCSrIYc3mbtExlD3GUmCph0_UPqE0Z1cEw&usqp=CAU)](https://main.gigasoft.com.pl/demo_TinyNotepad.mp4)

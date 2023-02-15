const AWS = require('aws-sdk');
const s3 = new AWS.S3();
const multer = require('multer');
const upload = multer();

const BUCKET_NAME = 'your-bucket-name';

// POST request to upload a file
app.post('/upload', upload.single('file'), async (req, res) => {
  const file = req.file;

  if (!file) {
    return res.status(400).send('No file uploaded.');
  }

  const fileName = file.originalname;
  const fileType = file.mimetype;

  // Check if file is a video
  if (fileType.startsWith('video/')) {
    const s3Params = {
      Bucket: BUCKET_NAME,
      Key: fileName,
      Body: file.buffer
    };

    try {
      const response = await s3.upload(s3Params).promise();
      console.log('File uploaded successfully:', response.Location);
      return res.send('Video uploaded successfully.');
    } catch (error) {
      console.error('Error uploading file:', error);
      return res.status(500).send('Error uploading file.');
    }
  }

  // Check if file is a JSON object
  if (fileType === 'application/json') {
    console.log('JSON object:', file.buffer.toString());
    return res.send('JSON object printed.');
  }

  return res.status(400).send('Invalid file type.');
});

// GET request to list uploaded videos
app.get('/videos', async (req, res) => {
  const s3Params = {
    Bucket: BUCKET_NAME,
    Delimiter: '/',
    Prefix: ''
  };

  try {
    const response = await s3.listObjectsV2(s3Params).promise();
    const videoObjects = response.Contents.filter(object => object.Key.startsWith('video/'));

    const videos = videoObjects.map(object => {
      return {
        url: `https://${BUCKET_NAME}.s3.amazonaws.com/${object.Key}`,
        name: object.Key.split('/')[1],
        lastModified: object.LastModified
      };
    });

    return res.send(videos);
  } catch (error) {
    console.error('Error listing videos:', error);
    return res.status(500).send('Error listing videos.');
  }
});

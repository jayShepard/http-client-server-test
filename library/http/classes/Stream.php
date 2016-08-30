<?php
namespace pillr\library\http;

use \Psr\Http\Message\StreamInterface as StreamInterface;

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{
    protected $stream;

    function __construct($source, $mode='r'){
        $this->stream = fopen($source, $mode);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        try{
            $this->rewind();
            $this->getContents();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        if (isset($this->stream)){
            $resource = $this->stream;
            unset($this->stream);
            return $resource;
        } else{
            return null;
        }

    }
    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        $stat = fstat($this->stream);
        if (isset($stat['size'])){
            return $stat['size'];
        }
        return null;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        $tell = ftell($this->stream);
        if ($tell === false){
            throw new \RuntimeException("Cannot retrieve current pointer position");
        } else{
            return $tell;
        }
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return (bool) feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $seek = fseek($this->stream, $offset, $whence);
        if($seek == -1){
            throw new \RuntimeException("Seek error");
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @see http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if(fseek($this->stream, 0) == -1){ //TODO: confirm this works
            throw new \RuntimeException("Steam is not seekable");
        }
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        $write_modes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];
        $mode = $this->getMetadata('mode');
        return in_array($mode, $write_modes);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string){
        $written = fwrite($this->stream, $string);
        if ($written === false){
            throw new \RuntimeException("Unable to write");
        } else{
            return $written;
        }
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        $read_modes = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];
        $mode = $this->getMetadata('mode');
        return in_array($mode, $read_modes);
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        $data = fread($this->stream, $length);
        if ($data === false){
            throw new \RuntimeException("Read error");
        }else{
            return empty($data) ? "" : $data;
        }
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents()
    {
        $content = stream_get_contents($this->stream);
        if ($content === false){
            throw new \RuntimeException("Read error");
        }else{
            return $content;
        }
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
       $meta =  stream_get_meta_data($this->stream);
        if ($key){
            empty($meta["$key"]) ? null : $meta["$key"];
        }else{
            return $meta;
        }
    }
}
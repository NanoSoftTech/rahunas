/*************************************************************************

  Copyright (C) 2014  Neutron Soutmun <neutron@rahunas.org>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

*************************************************************************/

#ifndef _RH_NETMASK_H
#define _RH_NETMASK_H

#include <inttypes.h>
#include <netinet/in.h>
#include <arpa/inet.h>

union ip_storage {
  uint32_t        all[4];
  uint32_t        ip;
  uint32_t        ip6[4];
  struct in_addr  in;
  struct in6_addr in6;
};

typedef union ip_storage IPStorage;

/*
 * Prefixlen maps for fast conversions, by Jan Engelhardt.
 */

#define E(a, b, c, d) \
	{.ip6 = { htonl(a), htonl(b), htonl(c), htonl(d), } }

/*
 * This table works for both IPv4 and IPv6;
 * just use prefixlen_netmask_map[prefixlength].ip.
 */
const IPStorage prefixlen_netmask_map[] = {
	E(0x00000000, 0x00000000, 0x00000000, 0x00000000),
	E(0x80000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xC0000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xE0000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xF0000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xF8000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFC000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFE000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFF000000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFF800000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFC00000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFE00000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFF00000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFF80000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFC0000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFE0000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFF0000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFF8000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFC000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFE000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFF000, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFF800, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFC00, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFE00, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFF00, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFF80, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFC0, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFE0, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFF0, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFF8, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFC, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFE, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0x00000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0x80000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xC0000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xE0000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xF0000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xF8000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFC000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFE000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFF000000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFF800000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFC00000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFE00000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFF00000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFF80000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFC0000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFE0000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFF0000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFF8000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFC000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFE000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFF000, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFF800, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFC00, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFE00, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFF00, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFF80, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFC0, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFE0, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFF0, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFF8, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFC, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFE, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0x00000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0x80000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xC0000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xE0000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xF0000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xF8000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFC000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFE000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFF000000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFF800000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFC00000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFE00000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFF00000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFF80000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFC0000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFE0000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFF0000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFF8000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFC000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFE000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFF000, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFF800, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFC00, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFE00, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFF00, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFF80, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFC0, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFE0, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFF0, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFF8, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFC, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFE, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0x00000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0x80000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xC0000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xE0000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xF0000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xF8000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFC000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFE000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFF000000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFF800000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFC00000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFE00000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFF00000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFF80000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFC0000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFE0000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFF0000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFF8000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFC000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFE000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFF000),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFF800),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFC00),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFE00),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFF00),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFF80),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFC0),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFE0),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFF0),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFF8),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFC),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFE),
	E(0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF, 0xFFFFFFFF),
};

inline
uint32_t
ip_netmask (uint8_t pfxlen)
{
  return prefixlen_netmask_map[pfxlen].ip;
}

inline
void ip_netmask6 (IPStorage *net, uint8_t pfxlen)
{
  net->ip6[0] &= prefixlen_netmask_map[pfxlen].ip6[0];
  net->ip6[1] &= prefixlen_netmask_map[pfxlen].ip6[1];
  net->ip6[2] &= prefixlen_netmask_map[pfxlen].ip6[2];
  net->ip6[3] &= prefixlen_netmask_map[pfxlen].ip6[3];
}

#endif // _RH_NETMASK_H
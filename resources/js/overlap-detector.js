/**
 * Fitur 5: Deteksi Overlap Antar Blok Lahan
 * Menggunakan Turf.js untuk cek intersect polygon.
 */
import * as turf from '@turf/turf';

window.OverlapDetector = {
    /**
     * Cek apakah polygon baru overlap dengan polygon yang sudah ada.
     * @param {Object} newGeoJson - GeoJSON polygon baru
     * @param {Array} existingBloks - Array of {nama, geojson} dari blok existing
     * @param {Number|null} excludeId - ID blok yang sedang diedit (dikecualikan dari cek)
     * @returns {Array} Array of overlap results [{blokNama, overlapArea}]
     */
    checkOverlap(newGeoJson, existingBloks, excludeId = null) {
        const overlaps = [];

        if (!newGeoJson || !newGeoJson.type) return overlaps;

        try {
            const newPolygon = this.toPolygon(newGeoJson);
            if (!newPolygon) return overlaps;

            existingBloks.forEach(blok => {
                if (!blok.geojson) return;

                try {
                    const existingPolygon = this.toPolygon(blok.geojson);
                    if (!existingPolygon) return;

                    const intersection = turf.intersect(
                        turf.featureCollection([newPolygon, existingPolygon]),
                        newPolygon,
                        existingPolygon
                    );

                    if (intersection) {
                        overlaps.push({
                            blokNama: blok.nama,
                            pemilik: blok.pemilik || '',
                        });
                    }
                } catch (e) {
                    // Skip invalid polygons
                }
            });
        } catch (e) {
            console.warn('OverlapDetector error:', e);
        }

        return overlaps;
    },

    /**
     * Validasi GeoJSON polygon.
     * @param {Object|string} geojson
     * @returns {Object} {valid, errors}
     */
    validatePolygon(geojson) {
        const errors = [];

        if (typeof geojson === 'string') {
            try {
                geojson = JSON.parse(geojson);
            } catch (e) {
                return { valid: false, errors: ['Format GeoJSON tidak valid (bukan JSON).'] };
            }
        }

        if (!geojson) {
            return { valid: false, errors: ['GeoJSON kosong.'] };
        }

        // Check type
        const type = geojson.type;
        if (type === 'Feature') {
            if (!geojson.geometry || geojson.geometry.type !== 'Polygon') {
                errors.push('Geometry harus bertipe Polygon.');
            }
        } else if (type === 'Polygon') {
            // OK
        } else if (type === 'FeatureCollection') {
            // Accept first feature
            if (!geojson.features || geojson.features.length === 0) {
                errors.push('FeatureCollection kosong.');
            }
        } else {
            errors.push('Tipe GeoJSON harus Polygon atau Feature.');
        }

        // Check coordinates count
        try {
            const coords = this.getCoordinates(geojson);
            if (coords && coords.length > 0) {
                const ring = coords[0];
                if (ring.length < 4) {
                    errors.push('Polygon harus memiliki minimal 4 titik koordinat.');
                }
            } else {
                errors.push('Koordinat polygon tidak ditemukan.');
            }
        } catch (e) {
            errors.push('Gagal membaca koordinat polygon.');
        }

        return { valid: errors.length === 0, errors };
    },

    /**
     * Hitung estimasi luas polygon dalam hektar.
     * @param {Object|string} geojson
     * @returns {Number|null} Luas dalam hektar
     */
    calculateArea(geojson) {
        try {
            if (typeof geojson === 'string') geojson = JSON.parse(geojson);
            const polygon = this.toPolygon(geojson);
            if (!polygon) return null;
            const area = turf.area(polygon); // m²
            return area / 10000; // convert to hectares
        } catch (e) {
            return null;
        }
    },

    /**
     * Convert various GeoJSON formats to turf polygon feature.
     */
    toPolygon(geojson) {
        if (!geojson) return null;
        if (typeof geojson === 'string') geojson = JSON.parse(geojson);

        if (geojson.type === 'Polygon') {
            return turf.polygon(geojson.coordinates);
        }
        if (geojson.type === 'Feature' && geojson.geometry?.type === 'Polygon') {
            return turf.polygon(geojson.geometry.coordinates);
        }
        if (geojson.type === 'FeatureCollection' && geojson.features?.length > 0) {
            const first = geojson.features[0];
            if (first.geometry?.type === 'Polygon') {
                return turf.polygon(first.geometry.coordinates);
            }
        }
        return null;
    },

    /**
     * Get coordinates from various GeoJSON formats.
     */
    getCoordinates(geojson) {
        if (geojson.type === 'Polygon') return geojson.coordinates;
        if (geojson.type === 'Feature') return geojson.geometry?.coordinates;
        if (geojson.type === 'FeatureCollection' && geojson.features?.length > 0) {
            return geojson.features[0].geometry?.coordinates;
        }
        return null;
    }
};

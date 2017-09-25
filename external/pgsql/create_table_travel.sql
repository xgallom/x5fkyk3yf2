CREATE TABLE travel
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  is_approved boolean,
  departure timestamp without time zone NOT NULL,
  travel_type_id integer NOT NULL,
  travel_provider_id integer,
  trip_id integer NOT NULL,
  city_from_id integer NOT NULL,
  city_to_id integer NOT NULL,
  spots integer,
  CONSTRAINT travel_pkey PRIMARY KEY (id),
  CONSTRAINT travel_fkey_city_from_id FOREIGN KEY (city_from_id)
  REFERENCES city (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_fkey_city_to_id FOREIGN KEY (city_to_id)
  REFERENCES city (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_fkey_travel_provider_id FOREIGN KEY (travel_provider_id)
  REFERENCES travel (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_fkey_travel_type_id FOREIGN KEY (travel_type_id)
  REFERENCES travel_type (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT travel_fkey_trip_id FOREIGN KEY (trip_id)
  REFERENCES trip (id) MATCH SIMPLE
  ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
OIDS=FALSE
);

CREATE INDEX fki_travel_fkey_travel_provider_id
  ON travel
  USING btree
  (travel_provider_id);

CREATE INDEX fki_travel_fkey_trip_id
  ON travel
  USING btree
  (trip_id);

